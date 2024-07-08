import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const FileService = {
  async get(params) {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/admin/setting/?page='+params.page+'&per_page='+params.per_page+'&keyword='+params.keyword
    if(params.is_paginate){
      url += '&is_paginate='+params.is_paginate
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
      .setUrl(URL_API.baseApiUrl + '/admin/file')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .setAttr('json',false)
      .trigger();
  },
  async update(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/setting/'+params.id)
      .setMethod(API_METHOD.PUT)
      .setParameters(params)
      .trigger();
  },
  async destroy(id) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/admin/setting/'+id)
      .setMethod(API_METHOD.DELETE)
      .setParameters()
      .trigger();
  },

}

export default FileService
