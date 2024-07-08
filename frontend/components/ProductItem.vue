<template>
  <Loading :loading="loading">
    <v-row>
      <v-col class="text-center">
        <v-col class="text-left">
          <v-card>
            <v-toolbar>
              <v-btn color="primary" dark class="mr-2" @click="editItem()">Thêm item mới</v-btn>
              <v-divider class="mx-4" inset vertical></v-divider>
              <v-btn color="primary" dark class="mr-2" @click="dialogImport = true">Import item</v-btn>
              <v-divider class="mx-4" inset vertical></v-divider>
              <a :href="getImage('file_import/temblade-import-product-item.csv')" class="mr-2 v-btn v-btn--is-elevated v-btn--has-bg theme--dark v-size--default primary">File mẫu</a>
            </v-toolbar>
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Tên</th>
                    <th class="text-left">Thông tin</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Ngày tạo</th>
                    <th class="text-left">Ngày sửa</th>
                    <th class="text-left"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in items" :key="item.id">
                    <td>{{ item.name }}</td>
                    <td>{{ item.description }}</td>
                    <td>{{ displayStatus(item.status) }}</td>
                    <td>{{ item.created_at }}</td>
                    <td>{{ item.updated_at }}</td>
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
                      <v-col cols="12">
                        <v-text-field v-model="editedItem.name" label="Tên"></v-text-field>
                        <p v-if="error && error.name" class="text-error">{{ error.name }}</p>
                      </v-col>
                      <v-col cols="12">
                        <v-textarea v-model="editedItem.description" label="Thông tin"></v-textarea>
                        <p v-if="error && error.value" class="text-error">{{ error.description }}</p>
                      </v-col>
                      <v-col cols="12">
                        <v-select v-model="editedItem.status" :items="itemStatus" item-text="display" item-value="value"
                          label="Trạng thái"></v-select>
                        <p v-if="error && error.status" class="text-error">{{ error.status }}</p>
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

            <v-dialog v-model="dialogImport">
              <v-card>
                <v-card-title>
                  <span class="text-h5">Import File CSV</span>
                </v-card-title>

                <v-card-text>
                  <v-container>
                    <v-file-input v-model="importItem.fileItem"  label="import file .csv" name="fileItem"></v-file-input>

                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue-darken-1" variant="text" :disabled="!importItem.fileItem" @click="importFile">
                    Import
                  </v-btn>
                  <v-btn color="blue-darken-1" variant="text" @click="dialogImport = false">
                    Close
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>

            <v-dialog v-model="dialogDelete" max-width="500px">
              <v-card>
                <v-card-title class="text-h5">Xóa tài khoản này?</v-card-title>
                <v-card-actions class="d-flex justify-center">
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
  </Loading>
</template>

<script>

import { API_CODE, PAGING, PRODUCT_ITEM_STATUS } from '~/utils/const';
import ProductService from '~/service/ProductService';
import Loading from '~/components/Loading.vue';
import JsCoreHelper from '~/utils/JsCoreHelper';
import CommonUtil from '~/utils/CommonUtil';

export default {
  name: 'ProductItem',
  components: { Loading },
  props: {
    productId: { type: Number },
  },
  data() {
    return {
      dialog: false,
      loading: false,
      selected: [],
      dialogDelete: false,
      dialogImport: false,
      query: {
        keyword: '',
        page: 1,
        per_page: PAGING.PerPage
      },
      error: [],
      totalPage: 1,
      items: [
      ],
      importItem: {},
      editedIndex: -1,
      editedItem: { status: 1 },
      defaultItem: { status: 1 },
      itemStatus: Object.values(PRODUCT_ITEM_STATUS)
    }
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'Thêm mới' : 'Chính sửa'
    },
  },
  watch: {
    productId() {
      this.initilize()
    },
    'query.page'(_newVal, _oldVal) {
      this.getData()
    },
    'query.per_page'(_newVal, _oldVal) {
      this.getData()
      this.getPaging()
    },
  },
  created() {
    this.initilize();
    this.importItem.product_id = this.productId;
  },
  methods: {
    initilize() {
      this.getData();
      this.getPaging();
    },
    async getData() {
      this.loading = true
      if (!this.productId) {
        return ''
      }
      const settingRes = await ProductService.getItems(this.productId, this.query)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.items = settingRes.data.records
      }
      this.loading = false
    },
    async getPaging() {
      if (!this.productId) {
        return ''
      }
      const queryPaging = { ...this.query }
      queryPaging.is_paginate = 1
      const settingRes = await ProductService.getItems(this.productId, queryPaging)
      if (settingRes && settingRes.status == API_CODE.Succeed) {
        this.totalPage = Math.ceil(settingRes.data.total_records / this.query.per_page)
      }
      return '';
    },

    editItem(index) {
      if (index != undefined) {
        this.editedItem = this.items[index]
        this.editedIndex = index
      }
      this.dialog = true
    },
    deleteItem(index) {
      this.editedIndex = index
      this.editedItem = this.items[index]
      this.dialogDelete = true
    },

    async deleteItemConfirm() {
      const apiRes = await ProductService.destroyItem(this.editedItem.id);
      if (apiRes && apiRes.status == API_CODE.DeleteSucceed) {
        this.getData();
        this.pushQuantity(apiRes.data.quantity);
      }
      this.closeDelete()
    },

    close() {
      this.loading = false
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
    getImage(url) {
        return CommonUtil.getImage(url)
    },
    async save() {
      this.editedItem.product_id = this.productId
      this.loading = true
      if (this.editedIndex > -1) {
        const saveRes = await ProductService.updateItem(this.editedItem.id, this.editedItem)
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.items[this.editedIndex] = saveRes.data;
          this.pushQuantity(saveRes.data.product.quantity);
          this.close();
        } else {
          if (saveRes && saveRes.data.errors) {
            this.error = saveRes.data.errors
          }
          JsCoreHelper.showErrorMsg(saveRes?.data?.message);
        }
      } else {
        const saveRes = await ProductService.createItem(this.editedItem)
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.$nextTick(() => {
            this.items.push(saveRes.data)
          })
          this.pushQuantity(saveRes.data.product.quantity);
          this.close();
        } else {
          if (saveRes && saveRes.data.errors) {
            this.error = saveRes.data.errors
          }
          JsCoreHelper.showErrorMsg(saveRes?.data?.message);
        }
      }
    },
    displayStatus(enumVal) {
      return CommonUtil.getEnumDisplay(enumVal, PRODUCT_ITEM_STATUS)
    },
    async importFile() {
      const formData = new FormData();
      const arrayKey = Object.keys(this.importItem);
      for (const i in arrayKey) {
        const key = arrayKey[i]
        formData.append(key, this.importItem[key]);
      }
      this.importItem = {};
      const importRes = await ProductService.import(formData)
      if (importRes && importRes.status == API_CODE.Succeed) {
          this.initilize();
          this.dialogImport = false;
          this.pushQuantity(importRes.data.quantity);
        }
      if (importRes && importRes.data.errors) {
        JsCoreHelper.showErrorMsg(importRes.data.message);
      }
    },
    pushQuantity(quantity) {
      this.$emit('getQuantity', quantity);
    } 
  }
}
</script>
