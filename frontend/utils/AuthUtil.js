import AuthService from "~/service/AuthService"
import LocalStorageHelper from "./LocalStorageHelper"
import { API_CODE } from "./const";

const AuthUtil = {
    async getCurrentUser(refresh){
        let user = LocalStorageHelper.getObject('user')
        if(refresh || !user.id && LocalStorageHelper.get('access_token')){
            const userRes = await AuthService.getCurrentUser();
            if(userRes && userRes.status === API_CODE.Succeed){
                user = userRes.data.user
                this.setCurrentUser(user)
            }else{
                this.logout()
            }
        }
        return user
    },
    setCurrentUser(user){
        LocalStorageHelper.setObject('user',user)
    },
    // check user can do permission
    async can(permission){
        const user = await this.getCurrentUser()
        if(user){
            if(user.role){
                for(const i in user.role){
                    if(user.role[i].type == 1){
                        return true
                    }
                }
            }
            if(user.permission){
                for(const i in user.permission){
                    if(user.permission[i].name === permission){
                        return true
                    }
                }
            }
        }
        return false
    },
    logout(){
        LocalStorageHelper.remove('access_token')
        LocalStorageHelper.remove('user')
        if (typeof window === 'undefined') return
        return window.location='/login'
    }

}

export default AuthUtil
