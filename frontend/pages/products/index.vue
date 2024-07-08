<template>
  <v-row class="product">
    <v-col class="text-center">
      <v-col class="text-left">
        <h2 class="title-page-sub">Sản phẩm</h2>
        <v-card>
          <v-card-title>
            <v-select v-model="query.category_id" :items="categories" item-text="name" item-value="id"
                          label="Danh mục sản phẩm" :clearable="true" multiselectable></v-select>
            <v-spacer></v-spacer>
            <v-text-field v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
              hide-details></v-text-field>
            <v-btn color="primary" dark class="" @click="initilize()">Tìm kiếm</v-btn>
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col v-for="(item,index) in items" :key="item.id" cols="12" sm="6" md="4" lg="3">
                <ClientProductItem :item="item"/>
              </v-col>
            </v-row>
          </v-card-text>
          <v-pagination v-model="query.page" :length="totalPage" :total-visible="7"></v-pagination>
        </v-card>
      </v-col>
    </v-col>
  </v-row>
</template>

<script>
import Loading from '~/components/Loading.vue'
import ClientProductItem from '~/components/Product/ClientProductItem.vue'
import ProductService from '~/service/ProductService'
import OrderService from '~/service/OrderService'
import JsCoreHelper from '~/utils/JsCoreHelper'
import { API_CODE, PAGING } from '~/utils/const'
import Category from '~/components/Category.vue'
import ProductItem from '~/components/ProductItem.vue'
import ProductCategoryService from '~/service/ProductCategoryService'
import CommonUtil from '~/utils/CommonUtil'

export default {
  name: 'Products',
  components: {
    Category,
    ProductItem,
    Loading,
    ClientProductItem
  },
  data: () => ({
    loading: false,
    selected: [],
    query: {
      keyword: '',
      page: 1,
      category_id: 0,
      per_page: PAGING.PerPage
    },
    totalPage: 1,
    items: [
    ],
    editedItem: { categories: [],image:'' },
    defaultItem: { categories: [],image:'' },
    categories: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'Thêm mới' : 'Chính sửa'
    },
  },

  async created() {
    this.query.category_id = parseInt(this.$route.query.cat)
    console.log(this.query)
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
    async getData() {
      const productRes = await ProductService.getClientProduct(this.query)
      if (productRes && productRes.status == API_CODE.Succeed) {
        this.items = productRes.data.records
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

    close() {
      this.loading = false
      this.dialog = false
    },

    closeDelete() {
      this.dialogDelete = false
    },
    getImage(image) {
      return CommonUtil.getImage(image)
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