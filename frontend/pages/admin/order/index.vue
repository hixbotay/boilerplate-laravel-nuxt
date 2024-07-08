<template>
    <v-row>
      <v-col class="text-center">
        <v-col class="text-left">
          <h2 class="title-page-sub">Quản lí đơn hàng</h2>
          <v-card>
            <v-card-title>
              <v-spacer></v-spacer>
              <v-text-field
                v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
                hide-details></v-text-field>
              <v-btn color="primary" dark class="" @click="initilize()">Tìm kiếm</v-btn>
            </v-card-title>
            <v-toolbar>
              <v-btn color="primary" dark class="mr-2" @click="editItem()">Thêm mới</v-btn>
              <v-divider class="mx-4" inset vertical></v-divider>
            </v-toolbar>
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Tài khoản</th>
                    <th class="text-left">
                      Email
                      <v-btn
                        v-show="!isOrderBy('email', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('email', 'DESC')">
                        <v-icon size="x-large">mdi-menu-up</v-icon>
                      </v-btn>
                      <v-btn
                        v-show="isOrderBy('email', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('email', 'ASC')">
                        <v-icon size="x-large">mdi-menu-down</v-icon>
                      </v-btn>
                    </th>
                    <th class="text-left">Số điện thoại</th>
                    <th class="text-left">Giá trị</th>
                    <th class="text-left">Trạng thái</th>
                    <th class="text-left">
                      Ngày tạo
                      <v-btn
                        v-show="!isOrderBy('created_at', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('created_at', 'DESC')">
                        <v-icon size="x-large">mdi-menu-up</v-icon>
                      </v-btn>
                      <v-btn
                        v-show="isOrderBy('created_at', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('created_at', 'ASC')">
                        <v-icon size="x-large">mdi-menu-down</v-icon>
                      </v-btn>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item,index) in items" :key="item.id">
                    <td>{{ getUser(item.user) }}</td>
                    <td>{{ item.email }}</td>
                    <td>{{ item.phone }}</td>
                    <td>{{ item.total }} vnđ</td>
                    <td>{{ getPaymentStatus(item.payment_status) }}</td>
                    <td>{{ item.created_at }}</td>
                    <td>
                      <v-icon size="small" class="me-2" @click="editItem(index)"> mdi-pencil </v-icon>
                      <v-icon size="small" @click="deleteItem(index)"> mdi-delete </v-icon>
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
            <v-pagination v-model="query.page" :length="totalPage" :total-visible="7"></v-pagination>
  
            <v-dialog v-model="dialog">
              <v-card>
                <v-card-title>
                  <span class="text-h5">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="12" sm="6" md="4">
                          <v-select
                            v-model="editedItem.user_id"
                            :disabled="inputDisable"
                            :items="users"
                            item-text="email"
                            item-value="id"
                            label="Tài khoản"
                        ></v-select>
                        <p v-if="error && error.role" class="text-error">{{ error.user_id }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field v-model="editedItem.email" label="Email" ></v-text-field>
                        <p v-if="error && error.email" class="text-error">{{ error.email }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field v-model="editedItem.phone" type="phone" label="Số điện thoại" ></v-text-field>
                        <p v-if="error && error.phone" class="text-error">{{ error.phone }}</p>
                      </v-col>
                      <v-col v-if="inputDisable" cols="12" sm="6" md="4">
                        <v-text-field v-model="editedItem.total" type="total" label="Giá trị"></v-text-field>
                        <p v-if="error && error.total" class="text-error">{{ error.total }}</p>
                      </v-col>
                      <v-col v-if="!inputDisable" cols="12" sm="6" md="4">
                          <v-select
                            v-model="editedItem.product_id"
                            :disabled="inputDisable"
                            :items="products"
                            item-text="name"
                            item-value="id"
                            label="Sản phẩm"
                        ></v-select>
                        <p v-if="error && error.role" class="text-error">{{ error.product_id }}</p>
                      </v-col>
                      <v-col v-if="!inputDisable" cols="12" sm="6" md="4" >
                        <v-text-field v-model="editedItem.count_product" type="number" min="0" label="Số lượng"></v-text-field>
                        <p v-if="error && error.count_product" class="text-error">{{ error.count_product }}</p>
                      </v-col>
                      
                      <v-col v-if="inputDisable" cols="12" sm="6" md="4">
                          <v-select
                            v-model="editedItem.payment_status"
                            :items="status"
                            item-text="state"
                            item-value="value"
                            label="Trạng thái"
                        ></v-select>
                        <p v-if="error && error.role" class="text-error">{{ error.role }}</p>
                      </v-col>
                      <v-row v-if="inputDisable">
                        <v-col cols="12">
                          <p>Ngày tạo {{ editedItem.created_at }}</p>
                          <p>Ngày cập nhật {{ editedItem.updated_at }}</p>
                        </v-col>
                      </v-row>
                    </v-row>
                  </v-container>
                </v-card-text>
  
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue-darken-1" variant="text" @click="close">
                    Cancel
                  </v-btn>
                  <v-btn color="blue-darken-1" :disabled="loading" variant="text" @click="save">
                    Save
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
  
            <v-dialog v-model="dialogDelete" max-width="500px">
              <v-card>
                <v-card-title class="text-h5">Bạn có muốn xóa tài hóa đơn {{editedItem.id}}?</v-card-title>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Cancel</v-btn>
                  <v-btn color="blue-darken-1" variant="text" @click="deleteItemConfirm">OK</v-btn>
                  <v-spacer></v-spacer>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-card>
        </v-col>
      </v-col>
    </v-row>
  </template>
  
  <script>
  import OrderService from '~/service/OrderService'
  import JsCoreHelper from '~/utils/JsCoreHelper'
  import { API_CODE, PAGING } from '~/utils/const'
  
  export default {
    name: 'Order',
    components: {  },
    layout: 'admin',
    data: () => ({
      dialog: false,
      loading: false,
      selected: [],
      middleware: ['auth'],
      dialogDelete: false,
      query: {
        keyword: '',
        page: 1,
        per_page: PAGING.PerPage
      },
      error: [],
      totalPage: 1,
      users:[],
      products:[],
      status:[
        { value: 1, state: 'Đang chờ giao dịch' },
        { value: 2, state: 'Đã duyệt' },
        { value: 3, state: 'Hủy bỏ' }
      ],
      items: [],
      editedIndex: -1,
      editedItem: {},
      defaultItem: {},
      
    }),
  
    computed: {
      formTitle() {
        return this.editedIndex === -1 ? 'Thêm mới' : 'Chính sửa'
      },
      inputDisable() {
        return !!this.editedItem.id;
      }
    },
    watch: {
      'query.page' (_newVal, _oldVal) {
        this.getData()
      },
      'query.per_page' (_newVal, _oldVal) {
        this.getData()
        this.getPaging()
      }
    },
  
    created() {
      this.initilize();
      this.getUsers();
      this.getProducts();
    },
  
    methods: {
      initilize() {
        this.getData();
        this.getPaging();
      },
      async getData() {
        const orderRes = await OrderService.get(this.query)
        // console.log(orderRes);
        if (orderRes && orderRes.status == API_CODE.Succeed) {
          this.items = orderRes.data.records
        }
      },
      async getPaging() {
        const queryPaging = {...this.query}
        queryPaging.is_paginate = 1
        const orderRes = await OrderService.get(queryPaging);
        if (orderRes && orderRes.status == API_CODE.Succeed) {
          this.totalPage = Math.ceil(orderRes.data.total_records / this.query.per_page)
        }
      },
      async getUsers() {
        const userRes = await OrderService.getUsers();
        if (userRes && userRes.status == API_CODE.Succeed) {
          this.users = userRes.data.records
        }
      },
      async getProducts() {
        const productRes = await OrderService.getproducts();
        if (productRes && productRes.status == API_CODE.Succeed) {
          this.products = productRes.data.records
        }
      },
   
      editItem(index) {
        // eslint-disable-next-line eqeqeq
        if(index!=undefined){
          this.editedItem = this.items[index];
          this.editedIndex = index;
        }
        this.dialog = true
      },
  
      deleteItem(index) {
        // eslint-disable-next-line eqeqeq
        if (index!=undefined) {
          this.editedIndex = index
          this.editedItem = this.items[index]
          this.dialogDelete = true
        }
      },
  
      async deleteItemConfirm() {
        const apiRes = await OrderService.destroy(this.editedItem.id)
        if (apiRes && apiRes.status == API_CODE.DeleteSucceed) {
          this.getData()
        }
        this.closeDelete();
      },
  
      close() {
        this.loading=false
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },
  
      closeDelete() {
        this.dialogDelete = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },
      setOrderBy(orderBy, orderType) {
        this.query.order_by = orderBy;
        this.query.order_type = orderType;
        this.getData();
      },
      isOrderBy(orderBy, orderType) {
        if (this.query.order_by == orderBy && this.query.order_type == orderType) {
          return true;
        }
        return false;
      },
      async save() {
        this.loading=true
        if (this.editedIndex > -1) {
            if ((typeof this.editedItem.role) !== 'number') { delete this.editedItem.role; }
            const saveRes = await OrderService.update(this.editedItem);
          if (saveRes && saveRes.status == API_CODE.Succeed) {
            this.items[this.editedIndex] = saveRes.data;
            this.close();
          } else {
            if(saveRes&&saveRes.data.errors){
              this.error = saveRes.data.errors
            }
            JsCoreHelper.showErrorMsg(saveRes.data.message);
          }
        } else {
          const saveRes = await OrderService.create(this.editedItem)
          if (saveRes && saveRes.status == API_CODE.Succeed) {
            this.items.push(saveRes.data);
            this.close();
          } else {
            if(saveRes&&saveRes.data.errors){
              this.error = saveRes.data.errors
            }
            JsCoreHelper.showErrorMsg(saveRes.data.message);
          }
        }
      },

      getUser(userObj) {
        if (userObj) {
          return userObj.email;
        }
        return null;
      },
      getPaymentStatus(statusNumber) {
        if (statusNumber === 1) {
          return 'Đang chờ giao dịch';
        }
        if (statusNumber === 2) {
          return 'Đã duyệt';
        }
        if (statusNumber === 3) {
          return 'Hủy bỏ';
        }
      }
    }
  }
  </script>
  
<style lang="scss" scoped>
</style>