<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" md="12">
        <h1 class="title-page">Trang chủ</h1>
        <v-card v-if="admin_notify">
          <v-card-title>
            <span class="text-h5">Thông báo</span>
          </v-card-title>
          <v-card-text v-html="admin_notify"></v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>
            <span class="text-h5">Giao dịch mới nhất</span>
          </v-card-title>
          <v-card-text>
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Mã giao dịch</th>
                    <th class="text-left">Nội dung</th>
                    <th class="text-left">Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in latestTransaction" :key="item.id">
                    <td>{{ item.id }}</td>
                    <td>{{ item.data }}</td>
                    <td>{{ item.created_at }}</td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>
            <span class="text-h5">Đơn hàng mới nhất</span>
          </v-card-title>
          <v-card-text>
            <v-simple-table>
              <template v-slot:default>
                <thead>
                  <tr>
                    <th class="text-left">Mã giao dịch</th>
                    <th class="text-left">Nội dung</th>
                    <th class="text-left">Ngày tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in latestOrder" :key="item.id">
                    <td>{{ item.id }}</td>
                    <td>{{ item.data }}</td>
                    <td>{{ item.created_at }}</td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row v-for="(item,index) in homePage" :key="index">
      <v-col cols="12" md="12">
        <v-card v-if="item.type==fieldType.category.id">
          <v-card-title v-if="item.name" class="title-section">{{ item.name }}</v-card-title>
          <v-card-text>
            <v-row>
              <v-col v-for="(product,indexProduct) in item.products" :key="indexProduct" cols="12" sm="6" md="4" lg="3">
                <ClientProductItem :item="product"/>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
        <v-card v-else>
          <v-card-title v-if="item.name" class="title-section">{{ item.name }}</v-card-title>
          <v-card-text v-html="item.text"></v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <Footer />
  </v-container>
</template>

<script>
import CardSummary from '@/components/Card/Summary.vue'
import ConfigService from '~/service/ConfigService'
import PaymentService from '~/service/PaymentService'
import PageService from '~/service/PageService'
import ClientProductItem from '~/components/Product/ClientProductItem.vue'
import OrderService from '~/service/OrderService'
import { API_CODE } from '~/utils/const'
import fieldType from '~/constants/fieldType'
export default {
  name: 'IndexPage',
  components: {
    CardSummary,
    ClientProductItem
  },
  data() {
    return {
      admin_notify: '',
      latestTransaction: [],
      latestOrder:[],
      homePage: [],
      fieldType: fieldType
    }
  },
  head() {
    return {
      title: 'Tài nguyên facebook',
      description:
        'Mua bán tài nguyên facebook'
    }
  },
  mounted() {
    this.getconfig()
    this.getHomePage()
    this.getTransactionHistory()
    this.getOrderHistory()
  },
  methods: {
    async getconfig() {
      const apires = await ConfigService.getClientConfig();
      if (apires && apires.status == API_CODE.Succeed) {
        this.admin_notify = apires.data.admin_notify
      }
    },
    async getHomePage() {
      const apires = await PageService.getHomePage()
      if (apires && apires.status == API_CODE.Succeed) {
        this.homePage = apires.data.records
      }
    },
    async getTransactionHistory() {
      const apires = await PaymentService.getClientLatest({is_purchase:1});
      if (apires && apires.status == API_CODE.Succeed) {
        this.latestTransaction = apires.data.records
      }
    },
    async getOrderHistory() {
      const apires = await PaymentService.getClientLatest({is_buy:1});
      if (apires && apires.status == API_CODE.Succeed) {
        this.latestOrder = apires.data.records
      }
    }
  }
}
</script>
