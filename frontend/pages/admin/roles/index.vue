<template>
    <v-row>
      <v-col class="text-center">
        <v-col class="text-left">
          <h2 class="title-page-sub">Quản lí phân quyền</h2>
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
                    <th class="text-left">
                      Name
                      <v-btn
                        v-show="!isOrderBy('name', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('name', 'DESC')">
                        <v-icon size="x-large">mdi-menu-up</v-icon>
                      </v-btn>
                      <v-btn
                        v-show="isOrderBy('name', 'DESC')"
                        variant="text"
                        icon=""
                        @click="setOrderBy('name', 'ASC')">
                        <v-icon size="x-large">mdi-menu-down</v-icon>
                      </v-btn>
                    </th>
                    <th class="text-left">Type</th>
                    <th class="text-left">Permission</th>
                    <th class="text-left">Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item,index) in items" :key="item.id">
                    <td>{{ item.name }}</td>
                    <td>{{ getType(item.type) }}</td>
                    <td><v-btn color="primary" variant="outlined" @click="editPermission(index)"> Phân quyền </v-btn></td>
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
                      <v-col cols="12" sm="6" md="6">
                          <v-select
                            v-model="editedItem.type"
                            :disabled="inputDisable"
                            :items="typeArr"
                            item-text="name"
                            item-value="value"
                            label="Type"
                        ></v-select>
                        <p v-if="error && error.role" class="text-error">{{ error.user_id }}</p>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field v-model="editedItem.name" label="Name" ></v-text-field>
                        <p v-if="error && error.name" class="text-error">{{ error.name }}</p>
                      </v-col>
                      <v-col v-if="inputDisable" cols="12">
                        <p>Quyền</p>
                        <v-chip v-for="(item) in editedItem.permission" :key="item.id" class="mb-2 mr-2" variant="outlined" :color="getColorPermission(item)" >{{ item }}</v-chip>
                      </v-col>
                      <v-row v-if="inputDisable">
                        <v-col cols="12">
                          <p>Ngày tạo {{ editedItem.created_at }}</p>
                          <p>Ngày cập nhật {{ editedItem.updated_at }}</p>
                        </v-col>
                      </v-row>
                    </v-row>
                  </v-container>
                  <v-container>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('GET')" >Method GET</v-chip>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('POST')" >Method POST</v-chip>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('PUT')" >Method PUT</v-chip>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('DELETE')" >Method DELETE</v-chip>
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
  
            <v-dialog v-model="dialogPermission">
              <v-card>
                <v-card-title>
                  <span class="text-h5">Phân quyền</span>
                </v-card-title>
                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col v-for="(item) in listPermission" :key="item.id" cols="12" sm="6" md="4">
                        <v-checkbox
                          v-model="editedItem.permission"
                          :label="item.name"
                          :value="item.name"
                          :color="colorMethod(item.method)"
                        ></v-checkbox>
                      </v-col>
                    </v-row>
                  </v-container>
                  <v-container>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('GET')" >Method GET</v-chip>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('POST')" >Method POST</v-chip>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('PUT')" >Method PUT</v-chip>
                    <v-chip class="mb-2 mr-2" variant="outlined" :color="colorMethod('DELETE')" >Method DELETE</v-chip>
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
                <v-card-title class="text-h5">Bạn có muốn xóa nhóm {{editedItem.name}}?</v-card-title>
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
  import RoleService from '~/service/RoleService';
  import JsCoreHelper from '~/utils/JsCoreHelper'
  import { API_CODE, PAGING } from '~/utils/const'
  
  export default {
    name: 'Roles',
    components: {  },
    layout: 'admin',
    data: () => ({
      dialog: false,
      loading: false,
      dialogDelete: false,
      dialogPermission: false,
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
      typeArr: [
        {name: 'Admin', value: 1},
        {name: 'Manager', value: 2},
        {name: 'Merchant', value: 3},
        {name: 'Partner', value: 4},
      ],
      listPermission: []
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
      this.getPermission();
    },
  
    methods: {
      initilize() {
        this.getData();
        this.getPaging();
      },
      async getData() {
        const roleRes = await RoleService.get(this.query)
        // console.log(orderRes);
        if (roleRes && roleRes.status == API_CODE.Succeed) {
          this.items = roleRes.data.records
        }
      },
      async getPaging() {
        const queryPaging = {...this.query}
        queryPaging.is_paginate = 1
        const roleRes = await RoleService.get(queryPaging);
        if (roleRes && roleRes.status == API_CODE.Succeed) {
          this.totalPage = Math.ceil(roleRes.data.total_records / this.query.per_page)
        }
      },
      async getPermission() {
        const permissionRes = await RoleService.getListPermission();
        if (permissionRes && permissionRes.status == API_CODE.Succeed) {
          this.listPermission = permissionRes.data.records
        }
        // console.log(this.listPermission);
      },
      editItem(index) {
        // eslint-disable-next-line eqeqeq
        if(index!=undefined){
          this.editedItem = this.items[index];
          this.editedIndex = index;
        }
        this.dialog = true
      },
      editPermission(index) {
        // eslint-disable-next-line eqeqeq
        if(index!=undefined){
          this.editedItem = this.items[index];
          this.editedIndex = index;
        }
        this.dialogPermission = true;
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
        const apiRes = await RoleService.destroy(this.editedItem.id)
        if (apiRes && apiRes.status == API_CODE.DeleteSucceed) {
          this.getData()
        }
        this.closeDelete();
      },
  
      close() {
        this.loading=false;
        this.dialog = false;
        this.dialogPermission = false
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
            const saveRes = await RoleService.update(this.editedItem);
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
          const saveRes = await RoleService.create(this.editedItem)
          if (saveRes && saveRes.status == API_CODE.Succeed) {
            this.items.unshift(saveRes.data);
            this.close();
          } else {
            if(saveRes&&saveRes.data.errors){
              this.error = saveRes.data.errors
            }
            JsCoreHelper.showErrorMsg(saveRes.data.message);
          }
        }
      }, 
      getType(typeNumber) {
        return this.typeArr.find((typeObj) => {
          return typeObj.value == typeNumber;
        }).name;
      },
      getColorPermission(routeName) {
        const perObj = this.listPermission.find((perrmission) => {
          return perrmission.name == routeName;
        });
        return this.colorMethod(perObj.method);
      },
      colorMethod(method) {
        const color = {
          'GET': 'blue',
          'POST': 'green',
          'PUT': 'yellow',
          'DELETE': 'red'
        };
        return color[method];
      }
    }
  }
  </script>
  
<style lang="scss" scoped>
</style>