import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const UserService = {
  async get(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/admin/users?page='+params.page+'&per_page='+params.per_page

    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate;
    }
    if(params.keyword){
      url += '&keyword='+params.keyword;
    }
    if (params.role_id) {
      url += '&role_id='+params.role_id;
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
      .setUrl(URL_API.baseApiUrl + '/admin/users/'+id)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async create(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/users')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },
  async update(params) {
    const baseWebApi = new BaseWebApi();
    const id = params.id;
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/users/'+id)
      .setMethod(API_METHOD.PUT)
      .setParameters(params)
      .trigger();
  },
  async destroy(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/users/'+id)
      .setMethod(API_METHOD.DELETE)
      .setParameters()
      .trigger();
  },
  async getRole() {
    const baseWebApi = new BaseWebApi();
    const url = URL_API.baseApiUrl + '/admin/roles'

    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },
  async getCountries() {
    const baseWebApi = new BaseWebApi();
    const url = URL_API.baseApiUrl + '/admin/countries/choose'

    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

  async updateClient(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/users/update')
      .setMethod(API_METHOD.PUT)
      .setParameters(params)
      .trigger();
  },
}

export default UserService
