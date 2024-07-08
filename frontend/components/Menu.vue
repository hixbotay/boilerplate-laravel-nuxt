<template>
  <v-list :dark="isDark" class ="client-menu">
    <slot v-for="(item, i) in items">
      <div v-if="item.display">
        <v-list-group v-if="item.items && item.items.length && item.to === null" :key="i"
          v-model="item.active" :prepend-icon="item.icon" :color="isDark ? 'warning' : 'accent-1'" :value="false"
          :ripple="false">
          <template v-if="item.items" v-slot:activator>
            <v-list-item-content>
              <v-list-item-title v-text="item.title"></v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item v-for="child in item.items" :key="child.title" :to="child.to">
            <template v-if="child.display">
              <v-list-item-action>
                <img v-if="child.image" :src="child.image" class="menu-icon"/>
                <v-icon v-else center dense>
                  {{ child.icon }}
                </v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title v-text="child.title"></v-list-item-title>
              </v-list-item-content>
            </template>
          </v-list-item>
        </v-list-group>
        <v-list-item v-else :key="item.title" :to="item.to" exact :ripple="false">
          <v-list-item-action>
            <img v-if="item.image" :src="item.image" class="menu-icon"/>
            <v-icon v-else center>
              {{ item.icon }}
            </v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title v-text="item.title"></v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </div>
    </slot>
  </v-list>
</template>

<script>
import AuthUtil from '~/utils/AuthUtil';
import ProductCategoryService from '~/service/ProductCategoryService';
import { API_CODE } from '~/utils/const';
import CommonUtil from '~/utils/CommonUtil';

export default {
  name: 'Menu',
  props: {
    isDark: { type: Boolean, default: false },
  },
  data() {
    return {
      items:[],
      user: false,
      categories:[]
    }
  },

  created(){
    this.init()
  },
  methods:{
    async init(){
      this.user = await AuthUtil.getCurrentUser();
      this.items = [
        {
          display: true,
          icon: 'mdi-apps',
          title: 'Trang chủ',
          to: '/',
        },
        {
          display: this.user.id,

          icon: 'mdi-package',
          title: 'Lịch sử giao dịch',
          to: '/payment-history',
        },
        {
          display: this.user.id,
          icon: 'mdi-package',
          title: 'Đơn hàng của bạn',
          to: '/order',
        },
      ]

      await this.getCategory()
      for(const i in this.categories){
        this.items.push({
          display: true,
          icon: 'mdi-store-settings',
          title: this.categories[i].name,
          to: '/products/?cat='+this.categories[i].id,
          image: CommonUtil.getImage(this.categories[i].image)
        })
        if(this.categories[i].child.length > 0){
          for(const j in this.categories[i].child){
            this.items.push({
              display: true,
              icon: '',
              title: '          - '+this.categories[i].child[j].name,
              to: '/products/?cat='+this.categories[i].child[j].id,
              image: CommonUtil.getImage(this.categories[i].child[j].image)
            })
          }
        }
      }
    },
    async getCategory(){
      const apiRes = await ProductCategoryService.getTreeCategory({});
      if (apiRes && apiRes.status == API_CODE.Succeed) {
        this.categories = apiRes.data.records
      }
    },
  }

  
}
</script>
