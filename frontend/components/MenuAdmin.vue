<template>
  <v-list :dark="isDark" class ="client-menu">
    <slot v-for="(item, i) in items">
      <div v-if="item.display">
        <v-list-group v-if="item.items && item.items.length && item.to === null" :key="i" v-model="item.active"
          :prepend-icon="item.icon" :color="isDark ? 'warning' : 'accent-1'" :value="false" :ripple="false">
          <template v-if="item.items" v-slot:activator>
            <v-list-item-content>
              <v-list-item-title v-text="item.title"></v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item v-for="child in item.items" :key="child.title" :to="child.to">
            <template v-if="child.display">
              <v-list-item-action>
                <v-icon center dense>
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
            <v-icon center>{{ item.icon }}</v-icon>
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

export default {
  name: 'MenuAdmin',
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
      this.user = await AuthUtil.getCurrentUser()

      this.items = [
        {
          display: true,
          icon: 'mdi-apps',
          title: 'Trang chủ',
          to: '/',
        },
        {
          display: process.env.envApi == 'dev',
          icon: 'mdi-account-card',
          title: 'Sample pages',
          to: null,
          items: [
            {
              display: true,
              icon: 'mdi-chart-bar',
              title: 'Charts',
              to: '/sample-pages/charts'
            },
            {
              display: true,
              icon: 'mdi-table-large',
              title: 'Tables',
              to: '/sample-pages/tables'
            },
            {
              display: true,
              icon: 'mdi-format-list-bulleted',
              title: 'Tabs',
              to: '/sample-pages/tabs'
            },
            {
              display: true,
              icon: 'mdi-message-bulleted',
              title: 'Forms',
              to: '/sample-pages/forms'
            },
            {
              display: true,
              icon: 'mdi-cards',
              title: 'Cards',
              to: '/sample-pages/cards'
            },
            {
              display: true,
              icon: 'mdi-flag',
              title: 'Filters',
              to: '/sample-pages/filters'
            },
            {
              display: true,
              icon: 'mdi-material-ui',
              title: 'Icons',
              to: '/sample-pages/icons'
            },
            {
              display: true,
              icon: 'mdi-pencil-box',
              title: 'Typography',
              // to: '/sample-pages/typography',
              to: null,
              items: [
                {
                  display: true,
                  icon: 'mdi-pencil-lock',
                  title: 'Paragraph',
                  to: '/sample-pages/paragraph'
                },
                {
                  display: true,
                  icon: 'mdi-pencil',
                  title: 'Headings',
                  to: '/sample-pages/headings'
                },
                {
                  display: true,
                  icon: 'mdi-grease-pencil',
                  title: 'Typographies',
                  to: '/sample-pages/typography'
                }
              ]
            },
            {
              display: true,
              icon: 'mdi-book-multiple',
              title: 'Landing',
              to: '/sample-pages/landing'
            }
          ]
        },
        {
          display: await AuthUtil.can('admin.list.payment'),
          icon: 'mdi-key',
          title: 'Quyền hạn',
          to: '/admin/roles',
        },
        {
          display: await AuthUtil.can('admin.list.products'),
          icon: 'mdi-cart',
          title: 'Sản phẩm',
          to: null,
          items:[
            {
              display: await AuthUtil.can('admin.list.products'),
              icon: 'mdi-cart',
              title: 'Sản phẩm',
              to: '/admin/product',
            },
            {
              display: await AuthUtil.can('admin.list.products'),
              icon: 'mdi-apps',
              title: 'Danh mục sản phẩm',
              to: '/admin/product/category',
            },
          ]
        },
        {
          display: await AuthUtil.can('admin.list.payment'),
          icon: 'mdi-cash-sync',
          title: 'Thanh toán',
          to: '/admin/payment',
        },
        {
          display: await AuthUtil.can('admin.list.order'),
          icon: 'mdi-package',
          title: 'Đơn hàng',
          to: '/admin/order',
        },
        {
          display: await AuthUtil.can('admin.list.setting'),
          icon: 'mdi-cog-outline',
          title: 'Cấu hình',
          to: null,
          items: [
            {
              display: await AuthUtil.can('admin.edit.setting'),
              icon: 'mdi-cog-outline',
              title: 'Cấu hình',
              to: '/admin/setting',
            },
            {
              display: await AuthUtil.can('admin.edit.setting'),
              icon: 'mdi-cog-outline',
              title: 'Cấu hình trang chủ',
              to: '/admin/setting/home-page',
            }
          ]
        },
        {
          display: await AuthUtil.can('admin.list.user'),
          icon: 'mdi-account-circle',
          title: 'Quản lý Users',
          to: '/admin/user',
        },
        {
          display: await AuthUtil.can('admin.list.article'),
          icon: 'mdi-book-open-variant',
          title: 'Quản lý Bài viết',
          to: '/admin/article',
        },
      ]
    },
  }


}
</script>
