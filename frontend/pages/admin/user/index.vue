<template>
    <v-row>
      <v-col class="text-center">
        <v-col class="text-left">
          <h2 class="title-page-sub">Quản lí tài khoản</h2>
          <v-card>
            <v-card-title>
              <v-spacer>
                <v-select 
                  v-model="query.role_id"
                  class="mt-6" 
                  :items="roles"
                  item-text="name" 
                  item-value="id" 
                  label="Quyền">
                </v-select>
              </v-spacer>
              <v-spacer></v-spacer>
              <v-text-field
                v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
                hide-details></v-text-field>
              <v-btn color="primary" dark class="" @click="setQueryURL()">Tìm kiếm</v-btn>
            </v-card-title>
            <v-toolbar>
              <v-btn color="primary" dark class="mr-2" @click="editItem()">Thêm mới</v-btn>
              <v-divider class="mx-4" inset vertical></v-divider>
            </v-toolbar>
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">
                      Email
                      <v-btn
                        v-show="!isOrderBy('email', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setQueryURL('email', 'DESC')">
                        <v-icon size="x-large">mdi-menu-up</v-icon>
                      </v-btn>
                      <v-btn
                        v-show="isOrderBy('email', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setQueryURL('email', 'ASC')">
                        <v-icon size="x-large">mdi-menu-down</v-icon>
                      </v-btn>
                    </th>
                    <th class="text-left">Số điện thoại</th>
                    <th class="text-left">
                      Họ tên
                      <v-btn
                        v-show="!isOrderBy('full_name', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setQueryURL('full_name', 'DESC')">
                        <v-icon size="x-large">mdi-menu-up</v-icon>
                      </v-btn>
                      <v-btn
                        v-show="isOrderBy('full_name', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setQueryURL('full_name', 'ASC')">
                        <v-icon size="x-large">mdi-menu-down</v-icon>
                      </v-btn>
                    </th>
                    <th class="text-left">Ví</th>
                    <th class="text-left">Trạng thái</th>
                    <th class="text-left">
                      Ngày tạo
                      <v-btn
                        v-show="!isOrderBy('created_at', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setQueryURL('created_at', 'DESC')">
                        <v-icon size="x-large">mdi-menu-up</v-icon>
                      </v-btn>
                      <v-btn
                        v-show="isOrderBy('created_at', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setQueryURL('created_at', 'ASC')">
                        <v-icon size="x-large">mdi-menu-down</v-icon>
                      </v-btn>
                    </th>
                    <th class="text-left"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item,index) in items" :key="item.id">
                    <td>{{ item.email }}</td>
                    <td>{{ item.mobile }}</td>
                    <td>{{ item.full_name }}</td>
                    <td>{{ item.amount }}</td>
                    <td>{{ item.status==1?'Active':'Block' }}</td>
                    <td>{{ getTime(item.created_at) }}</td>
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
                        <v-text-field v-model="editedItem.email" label="Email" :disabled="inputDisable"></v-text-field>
                        <p v-if="error && error.email" class="text-error">{{ error.email }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field v-model="editedItem.password" type="password" label="Password"></v-text-field>
                        <p v-if="error && error.password" class="text-error">{{ error.password }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field v-model="editedItem.mobile" label="Số điện thoại"></v-text-field>
                        <p v-if="error && error.mobile" class="text-error">{{ error.mobile }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field v-model="editedItem.full_name" label="Họ tên"></v-text-field>
                        <p v-if="error && error.full_name" class="text-error">{{ error.full_name }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                          <v-select
                            v-model="editedItem.role"
                            :items="roles"
                            item-text="name"
                            item-value="id"
                            label="Quyền hạn"
                        ></v-select>
                        <p v-if="error && error.role" class="text-error">{{ error.role }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                          <v-select
                            v-model="editedItem.status"
                            :items="status"
                            item-text="state"
                            item-value="value"
                            label="Trạng thái"
                        ></v-select>
                        <p v-if="error && error.role" class="text-error">{{ error.role }}</p>
                      </v-col>
                      <v-row v-if="editedItem.id">
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
                <v-card-title class="text-h5">Bạn có muốn xóa tài khoản {{editedItem.email}}?</v-card-title>
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
  import UserService from '~/service/UserService';
  import DateTimeUtil from '~/utils/DateTimeUtil';
  import JsCoreHelper from '~/utils/JsCoreHelper';
  import ParameterHelper from '~/utils/ParameterHelper';
  import { API_CODE, PAGING } from '~/utils/const';
  
  export default {
    name: 'User',
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
      roles:[],
      status:[
        { value: 0, state: 'Block' },
        { value: 1, state: 'Active' }
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
    mounted() {
      this.getQueryURL();
      this.initilize();
      this.getRole();
    },
    created() {},
    methods: {
      initilize() {
        this.getData();
        this.getPaging();
      },
      async getData() {
        const userRes = await UserService.get(this.query)
        // console.log(userRes);
        if (userRes && userRes.status == API_CODE.Succeed) {
          this.items = userRes.data.records
        }
      },
      async getPaging() {
        const queryPaging = {...this.query}
        queryPaging.is_paginate = 1
        const settingRes = await UserService.get(queryPaging)
        if (settingRes && settingRes.status == API_CODE.Succeed) {
          this.totalPage = Math.ceil(settingRes.data.total_records / this.query.per_page)
        }
      },
      async getRole() {
        const roleRes = await UserService.getRole()
        if (roleRes && roleRes.status == API_CODE.Succeed) {
            this.roles = roleRes.data.records;
        }
      },
      async getCountries() {
        const countryRes = await UserService.getCountries()
        if (countryRes && countryRes.status == API_CODE.Succeed) {
            this.countries = countryRes.data.records;
        }
      },
   
      async editItem(index) {
        // eslint-disable-next-line eqeqeq
        if(index!=undefined){
          const apiRes = await UserService.detail(this.items[index].id)
          if (apiRes && apiRes.status == API_CODE.Succeed) {
            this.editedItem = apiRes.data;
            this.editedItem.role = this.editedItem.role[0];
          }
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
        const apiRes = await UserService.destroy(this.editedItem.id)
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
      setQueryURL(orderBy = '', orderType = '') {
        if (orderBy) {
          this.query.order_by = orderBy;
        }
        if (orderType) {
          this.query.order_type = orderType;
        }
        ParameterHelper.setParam(this.convertQuery());
      },
      isOrderBy(orderBy, orderType) {
        if (this.query.order_by == orderBy && this.query.order_type == orderType) {
          return true;
        }
        return false;
      },
      convertQuery() {
        const queryConvert = {...this.query};
        delete queryConvert.page;
        delete queryConvert.per_page;
        return queryConvert;
      },
      getQueryURL() {
        const queryObj = ParameterHelper.getParams();
        this.query = {...this.query, ...queryObj};
      },
      async save() {
        this.loading=true
        if (this.editedIndex > -1) {
            if ((typeof this.editedItem.role) !== 'number') { delete this.editedItem.role; }
            const saveRes = await UserService.update(this.editedItem);
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
          const saveRes = await UserService.create(this.editedItem)
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
      getRoleName(roleArr) {
        let html = '';
        if (roleArr.length > 0) {
          roleArr.forEach((role, index) => {
            if (index > 0) {
              html += ', ' + role.name;
            } else {
              html += role.name;
            }
          });
        }
        return html;
      },
      getTime(date) {
        return DateTimeUtil.getYYYYMMDD(date, '-');
      }
    }
  }
  </script>
  
<style lang="scss" scoped>
</style>