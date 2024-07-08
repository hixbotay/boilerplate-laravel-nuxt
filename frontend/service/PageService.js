import BaseWebApi from '~/utils/BaseWebApi'
import { URL_API, API_METHOD } from '~/utils/const'

const PageService = {
  async getHomePage() {
    const baseWebApi = new BaseWebApi();
    let url = URL_API.baseApiUrl + '/setting/homepage'
    return await baseWebApi
      .setUrl(url)
      .setMethod(API_METHOD.GET)
      .setParameters()
      .trigger();
  },


}

export default PageService
