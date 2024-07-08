import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const OrderService = {
  async get(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/admin/order?page='+params.page+'&per_page='+params.per_page

    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate;
    }
    if(params.keyword){
      url += '&keyword='+params.keyword;
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

  async detail(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/order/'+id)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async create(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/order')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },
  async update(params) {
    const baseWebApi = new BaseWebApi();
    const id = params.id;
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/order/'+id)
      .setMethod(API_METHOD.PUT)
      .setParameters(params)
      .trigger();
  },
  async destroy(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/order/'+id)
      .setMethod(API_METHOD.DELETE)
      .setParameters()
      .trigger();
  },

  async getUsers() {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/users?status=1')
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async getproducts() {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/products?quantity_min=1')
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async getClient(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/order?page='+params.page+'&per_page='+params.per_page
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate;
    }
    if(params.keyword){
      url += '&keyword='+params.keyword;
    }
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
  async detaillient(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/order/'+id)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async buy(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/client-buy')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },

  async getClientLatest(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/order/latest')
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
}

export default OrderService
