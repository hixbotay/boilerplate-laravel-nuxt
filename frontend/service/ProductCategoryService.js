import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const ProductCategoryService = {
  async get(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/admin/products/category/?page='+(params.page?params.page:1)
    if(params.per_page){
      url += '&per_page='+params.per_page
    }
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate
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
  async getTreeCategory(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/products/tree-category/?page='+(params.page?params.page:1)
    if(params.per_page){
      url += '&per_page='+params.per_page
    }
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate
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

  async create(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/category')
      .setMethod(API_METHOD.POST)
      .setAttr('json',false)
      .setParameters(params)
      .trigger();
  },
  async update(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/category/'+params.get('id'))
      .setMethod(API_METHOD.POST)
      .setAttr('json',false)
      .setParameters(params)
      .trigger();
  },
  async destroy(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products/category/'+id)
      .setMethod(API_METHOD.DELETE)
      .setParameters()
      .trigger();
  },

}

export default ProductCategoryService
