import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const AdminService = {
  async getUserRegisterForMonth(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + `/admin/users/?created_at_min=${params.timeBegin}&created_at_max=${params.timeEnd}&is_paginate=1`;
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
  async getPurchaseForMonth(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + `/admin/payment/?created_at_min=${params.timeBegin}&created_at_max=${params.timeEnd}&order_id=0&status=2`;
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
  async getBuyProductItemForMonth(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + `/admin/order/product_item/list?created_at_min=${params.timeBegin}&created_at_max=${params.timeEnd}&is_paginate=1`;
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

}

export default AdminService
