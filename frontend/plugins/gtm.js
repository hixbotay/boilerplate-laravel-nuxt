
import LogUtil from "../utils/LogUtil"
export default async ({ app }) => {
  app.router.afterEach((to, from) => {
    try {
      app.$gtm.push({
        event: 'screen_view',
      })
    } catch (error) {
      LogUtil.log('GTM exception '+error)
    }
  })
}