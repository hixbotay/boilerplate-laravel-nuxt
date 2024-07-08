import { URL_API } from "./const";

const CommonUtil = {
    getImage(img){
        if(typeof img == 'string'){
            return URL_API.baseApiUrl+'/'+img;
        }else{
            return img;
        }
    },
    getEnumDisplay(value, eData){
        const keys = Object.keys(eData)
        for(const i in keys){
            const key = keys[i]
            if(eData[key].value == value){
                return eData[key].display
            }
        }
        return ''
    },
    compareVersionNumbers(v1, v2){
        const v1parts = v1.split('.');
        const v2parts = v2.split('.');
        for (let i = 0; i < v1parts.length; ++i) {
            if (v2parts.length === i) {
                return 1;
            }
            if (parseInt(v1parts[i]) > parseInt(v2parts[i])) {
                return 1;
            }
            if (parseInt(v1parts[i]) < parseInt(v2parts[i])) {
                return -1;
            }
        }
    
        if (v1parts.length !== v2parts.length) {
            return -1;
        }
    
        return 0;
    },
    displayMoney(value){
        try{
            return value.toLocaleString()+'đ'
        }catch(e){
            return value
        }
    },
    formatCurrency(price, currency = 'USD') {
        const formatter = new Intl.NumberFormat(
            'en-US', 
            {
                style: 'currency',
                currency,
                minimumFractionDigits: 2
            });
        return formatter.format(price);
    }
}

export default CommonUtil
