<template>
    <v-row>
      <v-col class="text-center">
        <v-col class="text-left">
          <h2 class="title-page-sub">Quản lí đơn hàng</h2>
          <v-card>
            <v-card-title>
              <v-spacer></v-spacer>
              <v-text-field v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
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
                    <th class="text-left">
                      Tiêu đề
                      <v-btn
                        v-show="!isOrderBy('title', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('title', 'DESC')">
                        <v-icon size="x-large">mdi-menu-up</v-icon>
                      </v-btn>
                      <v-btn
                        v-show="isOrderBy('title', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('title', 'ASC')">
                        <v-icon size="x-large">mdi-menu-down</v-icon>
                      </v-btn>
                    </th>
                    <th class="text-left">Người đăng</th>
                    <th class="text-left">Nội dung bài viết</th>
                    <th class="text-left">Tóm tắt</th>
                    <th class="text-left">Ảnh đại diện</th>
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
                    <td>{{ item.title }}</td>
                    <td>{{ item.user.full_name }}</td>
                    <td>{{ compactString(item.description, 50) }}</td>
                    <td>{{ compactString(item.excerpt, 25) }}</td>
                    <td><img v-if="item.image_feature" :src="getImage(item.image_feature)" class="w-100 d-block" /></td>
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
                      <v-col cols="12" sm="6">
                        <v-text-field v-model="editedItem.title" label="Tiêu đề" ></v-text-field>
                        <p v-if="error && error.title" class="text-error">{{ error.title }}</p>
                      </v-col>
                      <v-col cols="12" sm="6">
                          <v-file-input v-model="editedItem.image_feature" label="Ảnh"
                          name="image_feature"></v-file-input>
                        </v-col>
                        <v-col cols="12" sm="12">
                            <v-textarea v-model="editedItem.excerpt" label="Tóm tắt bài viết"></v-textarea>
                            <p v-if="error && error.excerpt" class="text-error">{{ error.excerpt }}</p>
                        </v-col>
                        <v-col cols="12" sm="12" >
                            <label for="">Nội dung bài viết</label>
                            <Editor v-model="editedItem.description"  />
                            <p v-if="error && error.description" class="text-error">{{ error.description }}</p>
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
                <v-card-title class="text-h5">Bạn có muốn xóa bài viết {{editedItem.title}}?</v-card-title>
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
  import ArticleService from '~/service/ArticleService'
import Editor from '~/components/Editor.vue'
  import JsCoreHelper from '~/utils/JsCoreHelper'
  import { API_CODE, PAGING } from '~/utils/const'
import CommonUtil from '~/utils/CommonUtil'
  export default {
    name: 'Article',
    components: { Editor },
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
    },
  
    methods: {
        initilize() {
            this.getData();
            this.getPaging();
        },
        async getData() {
            const orderRes = await ArticleService.get(this.query)
            // console.log(orderRes);
            if (orderRes && orderRes.status == API_CODE.Succeed) {
            this.items = orderRes.data.records
            }
        },
        async getPaging() {
            const queryPaging = {...this.query}
            queryPaging.is_paginate = 1
            const orderRes = await ArticleService.get(queryPaging);
            if (orderRes && orderRes.status == API_CODE.Succeed) {
            this.totalPage = Math.ceil(orderRes.data.total_records / this.query.per_page)
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
            const apiRes = await ArticleService.destroy(this.editedItem.id)
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
            if ((typeof this.editedItem.image_feature) !== 'object') { delete this.editedItem.image_feature; }
            const formData = this.getFormData(this.editedItem);
            if (this.editedIndex > -1) {
                const saveRes = await ArticleService.update(formData);
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
                const saveRes = await ArticleService.create(formData)
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
        getFormData(data) {
            const formData = new FormData();
            const arrayKey = Object.keys(data);
            for (const i in arrayKey) {
                const key = arrayKey[i]
                formData.append(key, data[key]);
            }
            return formData;
        },
        getImage(image) {
            return CommonUtil.getImage(image)
        },
        compactString(str, index) {
            const strArr = str.split(' ');
            const sortArr = strArr.splice(0, index)
            return sortArr.join( [' '] )
        }
    }
  }
  </script>
  
<style lang="scss" scoped>
</style>