import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const AuthService = {
  async login(params) {
    console.log(params,params)
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/login')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },

  async register(params) {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/register')
      .setMethod(API_METHOD.POST)
      .setParameters(params)
      .trigger();
  },

  async getCurrentUser() {
    const baseWebApi = new BaseWebApi();
    return await baseWebApi
      .setUrl(URL_API.baseApiUrl + '/auth')
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },

}

export default AuthService
