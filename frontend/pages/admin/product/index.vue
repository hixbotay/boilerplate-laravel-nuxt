<template>
  <v-row class="product">
    <v-col class="text-center">
      <v-col class="text-left">
        <h2 class="title-page-sub">Quản lí sản phẩm</h2>
        <v-card>
          <v-card-title>
            <v-spacer>
              <v-select 
                v-model="query.cat_id"
                class="mt-6" 
                :items="listCatSelect"
                item-text="name" 
                item-value="id" 
                label="Danh mục">
              </v-select>
            </v-spacer>
            <v-spacer></v-spacer>
            <v-spacer>
              <v-text-field
                v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
                hide-details></v-text-field>
            </v-spacer>
            <v-btn color="primary" dark class="" @click="initilize()">Tìm kiếm</v-btn>
          </v-card-title>
          <v-toolbar flat>
            <v-btn color="primary" dark class="mr-2" @click="editItem()">Thêm mới</v-btn>
            <v-divider class="mx-4" inset vertical></v-divider>
          </v-toolbar>
          <v-simple-table>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">
                    <v-checkbox @click="checkAll"></v-checkbox>
                    <v-icon v-show="query.order_by=='id'" size="medium" @click="setOrdering('id')">mdi-menu-{{ query.order_type=='ASC'?'up':'down' }}</v-icon>
                  </th>
                  <th class="text-left">Name
                    <v-icon v-show="query.order_by=='name'" size="medium" @click="setOrdering('emanameil')">mdi-menu-{{ query.order_type=='ASC'?'up':'down' }}</v-icon>
                  </th>
                  <th class="text-left">
                    SKU
                    <v-btn
                      v-show="!isOrderBy('sku', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('sku', 'DESC')">
                      <v-icon size="x-large">mdi-menu-up</v-icon>
                    </v-btn>
                    <v-btn
                      v-show="isOrderBy('sku', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('sku', 'ASC')">
                      <v-icon size="x-large">mdi-menu-down</v-icon>
                    </v-btn>
                  </th>
                  <th class="text-left">Ảnh</th>
                  <th class="text-left">Danh mục</th>
                  <th class="text-left">
                    Sô lượng
                    <v-btn
                      v-show="!isOrderBy('quantity', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('quantity', 'DESC')">
                      <v-icon size="x-large">mdi-menu-up</v-icon>
                    </v-btn>
                    <v-btn
                      v-show="isOrderBy('quantity', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('quantity', 'ASC')">
                      <v-icon size="x-large">mdi-menu-down</v-icon>
                    </v-btn>
                  </th>
                  <th class="text-left">
                    Giá
                    <v-btn
                      v-show="!isOrderBy('price', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('price', 'DESC')">
                      <v-icon size="x-large">mdi-menu-up</v-icon>
                    </v-btn>
                    <v-btn
                      v-show="isOrderBy('price', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('price', 'ASC')">
                      <v-icon size="x-large">mdi-menu-down</v-icon>
                    </v-btn>
                  </th>
                  <th class="text-left">
                    Giá khuyến mãi
                    <v-btn
                      v-show="!isOrderBy('price_sale', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('price_sale', 'DESC')">
                      <v-icon size="x-large">mdi-menu-up</v-icon>
                    </v-btn>
                    <v-btn
                      v-show="isOrderBy('price_sale', 'DESC')"
                      variant="text"
                      icon=""
                      @click="setOrderBy('price_sale', 'ASC')">
                      <v-icon size="x-large">mdi-menu-down</v-icon>
                    </v-btn>
                  </th>
                  <th class="text-left"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in items" :key="item.id">
					        <td><v-checkbox v-model="itemIds" :value="item.id"></v-checkbox></td>
                  <td>{{ item.name }}</td>
                  <td>{{ item.sku }}</td>
                  <td><img v-if="item.image" :src="getImage(item.image)" class="product-image" /></td>
                  <td>
                    <v-chip v-for="(cat, catIndex) in item.cat_text" :key="catIndex" class="mr-2">{{ cat }}</v-chip>
                  </td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ item.price }}</td>
                  <td>{{ item.price_sale }}</td>

                  <td>
                    <v-icon size="small" class="me-2" @click="editItem(index)"> mdi-pencil </v-icon>
                    <v-icon size="small" @click="deleteItem(index)"> mdi-delete </v-icon>
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
		  <div v-if="notFound" class="text-center py-2">Not found</div>
          <v-pagination v-model="query.page" :length="totalPage" :total-visible="7"></v-pagination>

          <v-dialog v-model="dialog" fullscreen hide-overlay>
            <v-card>
              <v-toolbar color="primary" dark>
                <v-btn icon @click="close">
                  <v-icon>mdi-close</v-icon>
                </v-btn>
                <v-toolbar-title class="mr-2">{{ formTitle }}</v-toolbar-title>
              </v-toolbar>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" md="8">
                      <v-text-field v-model="editedItem.name" label="Tên sản phẩm"></v-text-field>
                      <p v-if="error && error.name" class="text-error">{{ error.name }}</p>
                      <v-text-field v-model="editedItem.sku" label="sku"></v-text-field>
                      <p v-if="error && error.sku" class="text-error">{{ error.sku }}</p>
                      <v-text-field v-model="editedItem.price" label="Giá một sản phẩm"></v-text-field>
                      <p v-if="error && error.price" class="text-error">{{ error.price }}</p>
                      <v-text-field v-model="editedItem.price_sale" label="Giá khuyến mại"></v-text-field>
                      <p v-if="error && error.price_sale" class="text-error">{{ error.price_sale }}</p>
                      <p>Số lượng {{ editedItem.quantity }}</p>
                      <p v-if="error && error.quantity" class="text-error">{{ error.quantity }}</p>
                      <v-text-field v-model="editedItem.excerpt" label="Tóm tất"></v-text-field>
                      <p v-if="error && error.excerpt" class="text-error">{{ error.excerpt }}</p>
                      <div>Tài khoản của sản phẩm:</div>
                      <ProductItem :productId="editedItem.id" @getQuantity="editedItem.quantity = $event" />
                      <div>Mô tả chi tiết</div>
                      <Editor v-model="editedItem.description" />
                      <p v-if="error && error.description" class="text-error">{{ error.description }}</p>
                      <p>Ngày tạo {{ editedItem.created_at }}</p>
                      <p>Ngày cập nhật {{ editedItem.updated_at }}</p>
                    </v-col>
                    <v-col cols="12" md="4">
                      <Category :inputSelected="editedItem.categories" @update-category="updateCategory"
                        @new-category="newCategory" :categories="categories" />
                      <v-file-input v-model="editedItem.image"  label="Ảnh" name="fileToUpload"></v-file-input>
                      <p><img v-if="editedItem.image" :src="getImage(editedItem.image)" class="w-100 d-block" /></p>
                      <p v-if="error && error.image" class="text-error">{{ error.image }}</p>
                      <v-card-actions>
                        <v-btn class="mr-2" color="blue-darken-1" variant="text" @click="close"> Cancel </v-btn>
                        <v-btn class="mr-2" color="blue-darken-1" variant="text" :disabled="loading" @click="save"> Save </v-btn>
                      </v-card-actions>
                    </v-col>

                  </v-row>
                </v-container>
              </v-card-text>
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
import Category from '~/components/Category.vue'
import Editor from '~/components/Editor.vue'
import ProductItem from '~/components/ProductItem.vue'
import ProductCategoryService from '~/service/ProductCategoryService'
import ProductService from '~/service/ProductService'
import CommonUtil from '~/utils/CommonUtil'
import JsCoreHelper from '~/utils/JsCoreHelper'
import { API_CODE, PAGING } from '~/utils/const'

export default {
  name: 'Product',
  layout: 'admin',
  components: {
    Category,
    ProductItem,
    Editor
  },
  data: () => ({
	notFound: false,
    loading: false,
    dialog: false,
    selected: [],
    middleware: ['auth'],
    dialogDelete: false,
    query: {
      keyword: '',
      page: 1,
      per_page: PAGING.PerPage,
      order_by: 'id',
      order_type: 'DESC',
    },
    error: [],
    totalPage: 1,
    items: [
    ],
    editedIndex: -1,
    editedItem: { categories: [],image:'' },
    defaultItem: { categories: [],image:'' },
    categories: []
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'Thêm mới' : 'Chính sửa'
    },
    listCatSelect() {
      const listCat = [];
      listCat.push({
            id: 0,
            name: 'Chọn danh mục'
          });
      if (this.categories.length > 0) {
        this.categories.forEach(cat => {
          listCat.push({
            id: cat.id,
            name: cat.name
          });
          if (cat.child.length > 0) {
            cat.child.forEach(child => {
              listCat.push({
                id: child.id,
                name: '  --  ' + child.name
              });
            });
          }
        });
      }
      return listCat;
    }
  },

  watch: {
    dialog(val) {
      val || this.close()
    },
    dialogDelete(val) {
      val || this.closeDelete()
    },
  },

  async created() {
    this.initilize()
    this.getCategory()
  },

  methods: {
    initilize() {
      this.getData()
      this.getPaging()
    },
    async getCategory(){
      const apiRes = await ProductCategoryService.getTreeCategory({})
      if (apiRes && apiRes.status == API_CODE.Succeed) {
        this.categories = apiRes.data.records
      }
    },
    convertCat(item) {
      item['cat_text'] = item.categories.map(function (e) { return e.name })
      item['categories'] = item.categories.map(function (e) { return e.id })
      return item
    },
    async getData() {
      const productRes = await ProductService.get(this.query)
      if (productRes && productRes.status == API_CODE.Succeed) {
        this.items = productRes.data.records
        for (const i in this.items) {
          this.items[i] = this.convertCat(this.items[i])
        }
		if(this.items.length == 0){
          this.notFound = true
        }else{
          this.notFound = false
        }
      }
    },
    async getPaging() {
      const queryPaging = { ...this.query }
      queryPaging['is_paginate'] = 1
      const productRes = await ProductService.get(queryPaging)
      if (productRes && productRes.status == API_CODE.Succeed) {
        this.totalPage = Math.ceil(productRes.data.total_records / this.query.per_page)
      }
    },

    editItem(index) {
      if (index != undefined) {
        this.editedItem = this.items[index];
        this.editedIndex = index
      }else{
        this.editedItem = this.defaultItem
        this.editedIndex = -1
      }
      this.dialog = true
    },

    deleteItem(index) {
      if (index != undefined) {
        this.editedIndex = index
        this.editedItem = this.items[index]
        this.dialogDelete = true
      }

    },

    async deleteItemConfirm() {
      this.closeDelete()
      const apiRes = await ProductService.destroy(this.editedItem.id)
      if(apiRes && apiRes.status == API_CODE.DeleteSucceed){
        this.$nextTick(() => {
          delete this.items[this.editedIndex]
        })
        this.editedIndex = -1
        if(this.items.length==0){
          this.getData
        }
      }else{
        JsCoreHelper.showErrorMsg(apiRes?.data?.message)
      }
    },

    close() {
      this.loading = false;
      this.dialog = false;
      this.editedItem = {};
      this.editedIndex = -1;
    },

    closeDelete() {
      this.dialogDelete = false
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
      this.loading = true
      const saveRes = await this.commitSave()
      this.loading = false
      if (this.editedItem.id) {
        if (saveRes && saveRes.status == API_CODE.Succeed) {
          this.$nextTick(() => {
            this.items[this.editedIndex] = this.convertCat(saveRes.data)
          })
          this.close()
        }
      } else if (saveRes && saveRes.status == API_CODE.Succeed) {
        this.items.push(this.convertCat(saveRes.data))
        this.close()
      }
      if (saveRes && saveRes.data.errors) {
        this.error = saveRes.data.errors
        JsCoreHelper.showErrorMsg(saveRes.data.message);
      }

    },

    async commitSave() {
      const formData = new FormData();
      const arrayKey = Object.keys(this.editedItem)
      for (const i in arrayKey) {
        const key = arrayKey[i]
        if (key == 'categories') {
          for (const j in this.editedItem[key]) {
            formData.append('categories[]', this.editedItem[key][j]);
          }
        } else {
          formData.append(key, this.editedItem[key]);
        }
      }

      return await ProductService.create(formData)
    },
    updateCategory(newCat) {
      this.editedItem.categories = newCat
    },
    async newCategory(newCatData) {
      const apiRes = await ProductCategoryService.create(newCatData);
      if (apiRes && apiRes.status == API_CODE.Succeed) {
        this.$nextTick(() => {
          if(apiRes.data.parent_id){
            for(const i in this.categories){
              if(this.categories[i].id == apiRes.data.parent_id){
                this.categories[i]
                if(!this.categories[i].child){
                  this.categories[i].child = []
                }
                this.categories[i].child.push(apiRes.data);
              }
            }
          }else{
            this.categories.push(apiRes.data);
          }
        })
        
        this.editedItem.categories.push(apiRes.data.id)
      }else{
        JsCoreHelper.showErrorMsg(apiRes?.data?.message)
      }
    },
    getImage(image) {
      if (typeof image === 'string') {
        return CommonUtil.getImage(image)
      }
      return null;
    },
    checkAll(){
      if(this.itemIds.length == 0){
        this.itemIds = this.items.map(function (el) { return el.id; });
      }else{
        this.itemIds = []
      }
    },
    setOrdering(field){
      if(this.query.order_by != field){
        this.query.order_by=field
        this.query.order_type = 'DESC'
      }else{
        this.query.order_type = this.query.order_type == 'DESC' ? 'ASC' : 'DESC'
      }
    },

  },
  watch: {
    'query.page': function (newVal, oldVal) {
      this.getData()
    },
    'query.per_page': function (newVal, oldVal) {
      this.getData()
      this.getPaging()
    },
  }
}
</script>
