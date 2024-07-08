import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const ProductService = {
  async get(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/admin/products/?page='+params.page+'&per_page='+params.per_page
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate
    }
    if(params.keyword){
      url += '&keyword='+params.keyword
    }
    if(params.cat_id){
      url += '&cat_id='+params.cat_id
    }
    if (params.order_by) {
      url += '&order_by='+params.order_by;
    }
    if (params.order_type) {
      url += '&order_type='+params.order_type;
    }
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
  async getClientProduct(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/products/?page='+params.page+'&per_page='+params.per_page
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate
    }
    if(params.keyword){
      url += '&keyword='+params.keyword
    }
    if(params.category_id){
      url += '&category_id='+params.category_id
    }
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
  

  async detail(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/'+id)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async clientDetail(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/products/'+id)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async create(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products')
      .setMethod(API_METHOD.POST)
      .setAttr('json',false)
      .setParameters(params)
      .trigger();
  },
  async update(id,params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/'+id)
      .setMethod(API_METHOD.PUT)
      .setAttr('json',false)
      .setParameters(params)
      .trigger();
  },
  async destroy(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/'+id)
      .setMethod(API_METHOD.DELETE)
      .setParameters()
      .trigger();
  },
  async getItems(productId, params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/admin/products/product-item/?product_id='+productId
    if(params.page){
      url += '&page='+params.page
    }
    if(params.per_page){
      url += '&per_page='+params.per_page
    }
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate
    }
    if(params.keyword){
      url += '&keyword='+params.keyword
    }
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
  async createItem(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/product-item')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },
  async updateItem(id,params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/product-item/'+id)
      .setMethod(API_METHOD.PUT)
      .setParameters(params)
      .trigger();
  },
  async destroyItem(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/product-item/'+id)
      .setMethod(API_METHOD.DELETE)
      .setParameters()
      .trigger();
  },

  async import(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/import/product-item')
      .setMethod(API_METHOD.POST)
      .setAttr('json',false)
      .setParameters(params)
      .trigger();
  },
}

export default ProductService
