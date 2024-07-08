import LocalStorageHelper from "~/utils/LocalStorageHelper";

export default function ({ store, redirect, route }) {
    // If the user is not authenticated
    if (!LocalStorageHelper.get('access_token')) {
      let path = ''
      if(route.path){
        path = encodeURIComponent(route.path);
      }
      return redirect(`/login?r=${path}`);
    }
  }