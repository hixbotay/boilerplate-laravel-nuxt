import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const PaymentService = {
  async get(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/admin/payment/?page='+(params.page?params.page:1)
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
  async getclientTransaction(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/transaction/?page='+(params.page?params.page:1)
    if(params.per_page){
      url += '&per_page='+params.per_page
    }
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate
    }
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async purchase(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/payment/purchase')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },

  async getQrcode(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/payment/qrcode')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },
  async detail(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/payment/'+id)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async create(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/payment')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },
  async update(id,params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/payment/'+id)
      .setMethod(API_METHOD.PUT)
      .setParameters(params)
      .trigger();
  },
  async destroy(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/payment/'+id)
      .setMethod(API_METHOD.DELETE)
      .setParameters()
      .trigger();
  },

  async approvePayment(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/payment/purchaseapprove/'+id)
      .setMethod(API_METHOD.POST)
      .trigger();
  },
  async cancelPayment(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/payment/purchasecancel/'+id)
      .setMethod(API_METHOD.POST)
      .trigger();
  },
  async getClientLatest(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/transaction/latest/'
    const query = []
    if(params.is_buy){
      query.push('is_buy=1')
    }
    if(params.is_purchase){
      query.push('is_purchase=1')
    }
    url += '?'+query.join('&')
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

}

export default PaymentService
