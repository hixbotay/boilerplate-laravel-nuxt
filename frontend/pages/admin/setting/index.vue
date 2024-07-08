<template>
  <v-row>
    <v-col class="text-center">
      <v-col class="text-left">
        <h2 class="title-page-sub">Quản lí cấu hình hệ thống</h2>
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
                    Key
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
                  <th class="text-left">Giá trị</th>
                  <th class="text-left">Ngày tạo</th>
                  <th class="text-left">Ngày sửa</th>
                  <th class="text-left"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item,index) in items" :key="item.id">
                  <td>{{ item.name }}</td>
                  <td ><div class="max-wdith-200" v-html="item.value"></div></td>
                  <td>{{ item.created_at }}</td>
                  <td>{{ item.updated_at }}</td>
                  <td>
                    <nuxt-link
                      v-for="(cat, catIndex) in item.categories" :key="catIndex"
                      :to="'/admin/settings/category/' + cat.id" class="mr-2">{{ item.categories[0].name }}</nuxt-link>
                  </td>
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
                    <v-col cols="12" md="2">
                      <v-text-field v-model="editedItem.name" label="Key"></v-text-field>
                      <p v-if="error && error.name" class="text-error">{{ error.name }}</p>
                    </v-col>
                    <v-col cols="12" md="10">
                      <Editor v-if="configField[editedItem.name] && configField[editedItem.name].type=='editor'" v-model="editedItem.value"/>
                      <v-textarea v-if="configField[editedItem.name] && configField[editedItem.name].type=='textarea'" v-model="editedItem.value"></v-textarea>
                      <v-textarea v-if="configField[editedItem.name] && configField[editedItem.name].type=='json'" v-model="editedItem.value"></v-textarea>
                      <v-textarea v-if="!configField[editedItem.name]" v-model="editedItem.value"></v-textarea>
                      <p v-if="error && error.value" class="text-error">{{ error.value }}</p>
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
              <v-card-title class="text-h5">Are you sure you want to delete this item?</v-card-title>
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
import ConfigService from '~/service/ConfigService'
import Editor from '~/components/Editor.vue'
import JsCoreHelper from '~/utils/JsCoreHelper'
import { API_CODE, PAGING } from '~/utils/const'
import configField from '~/constants/configField'

export default {
  name: 'Setting',
  components: { Editor },
  layout: 'admin',
  middleware: ['auth'],
  data: () => ({
    configField: configField,
    dialog: false,
    loading: false,
    selected: [],
    dialogDelete: false,
    query: {
      keyword: '',
      page: 1,
      per_page: PAGING.PerPage
    },
    error: [],
    totalPage: 1,
    items: [
    ],
    editedIndex: -1,
    editedItem: {},
    defaultItem: {},
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'Thêm mới' : 'Chính sửa'
    },
  },
  watch: {
    'query.page' (_newVal, _oldVal) {
      this.getData()
    },
    'query.per_page' (_newVal, _oldVal) {
      this.getData()
      this.getPaging()
    },
  },

  created() {
    this.initilize()
  },

  methods: {
    initilize() {
      this.getData()
      this.getPaging()
    },
    async getData() {
      const settingRes = await ConfigService.get(this.query)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.items = settingRes.data.records
      }
    },
    async getPaging() {
      const queryPaging = {...this.query}
      queryPaging['is_paginate'] = 1
      const settingRes = await ConfigService.get(queryPaging)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.totalPage = Math.ceil(settingRes.data.total_records / this.query.per_page)
      }
    },

    editItem(index) {
      if(index!=undefined){
        this.editedItem = this.items[index]
        this.editedIndex = index
      }else{
        this.editedItem = this.defaultItem
        this.editedIndex = -1
      }
      this.dialog = true
    },

    deleteItem(index) {
      this.editedIndex = index
      this.editedItem = this.items[index]
      this.dialogDelete = true
    },

    deleteItemConfirm() {
      const apiRes = ConfigService.destroy(this.editedItem.id)
      if (apiRes && apiRes == API_CODE.DeleteSucceed) {
        this.getData()

      }
      this.closeDelete()
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
      if (this.editedItem.id) {
        const saveRes = await ConfigService.update(this.editedItem)
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.items[this.editedIndex] = saveRes.data
          this.items = [...this.items]
          return this.close()
        } 
      } else {
        const saveRes = await ConfigService.create(this.editedItem)
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.$nextTick(() => {
            this.items.push(saveRes.data)
          })
          return this.close()
        } 
      }
      if(saveRes&&saveRes.data.errors){
        this.error = saveRes.data.errors
        return
      }
      JsCoreHelper.showErrorMsg(saveRes.data.message);
    },
  
  }
}
</script>
<style lang="scss" scoped>
.max-wdith-200{
  max-width:500px;
  max-height: 100px;
  overflow: auto;
}
</style>