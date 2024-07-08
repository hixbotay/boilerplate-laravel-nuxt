<template>
  <div>
    <v-container fluid justify-center>
      <h2 class="title-page-sub">Quản lí giao diện trang chủ</h2>
      <v-flex class="pa-3 ma-2">
        <v-toolbar class="mb-2" color="primary" dark>
          <v-select :items="configFieldArr" v-model="chooseNewCat" label="Chọn loại item" item-text="name" item-value="id" class="mr-2"></v-select>
          <v-btn @click="newCat" class="mr-2">Thêm</v-btn>
          <v-btn @click="save" :disabled="loading">Lưu</v-btn>
        </v-toolbar>
        <v-list>
          <draggable v-model="items">
            <div v-for="(item, index) in items" :key="index" class="d-flex justify-space-between">
              <div v-if="item.type==configField.category.id" class="d-flex">
                <v-select :items="category" v-model="item.id" label="Chọn danh mục" item-text="name" item-value="id" @change="changeCat(index)"></v-select>
                <v-text-field v-model="item.name" label="Tên danh mục"></v-text-field>
                <v-text-field v-model="item.limit" type="number" label="Số lượng sản phẩm hiển thị"></v-text-field>
              </div>
              <div v-if="item.type==configField.text.id" class="w-100">
                <v-textarea v-model="item.text" solo auto-grow full-width></v-textarea>
              </div>
              <div v-if="item.type==configField.editor.id" class="w-100">
                <Editor v-model="item.text" />
              </div>
              
              <v-btn icon @click="removeCat(index)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </div>
          </draggable>
        </v-list>
      </v-flex>
    </v-container>
  </div>
</template>

<script>
import { API_CODE } from '~/utils/const'
import ConfigService from '~/service/ConfigService'
import JsCoreHelper from '~/utils/JsCoreHelper'
import fieldType from '~/constants/fieldType'
import ProductCategoryService from '~/service/ProductCategoryService'
import draggable from "vuedraggable";

export default {
  name: 'HomePageSetting',
  components: {
    draggable
  },
  data() {
    return {
      id: 0,
      loading: true,
      items: [],
      name: 'home_page',
      category: [],
      chooseNewCat: 0,
      configField: fieldType,
      configFieldArr: Object.values(fieldType)
    }
  },
  mounted() {
    this.getData()
    this.getCategory()
  },
  methods: {
    async getData(){
      this.loading = true
      const apiRes = await ConfigService.get({name:this.name})
      this.loading = false
      if (apiRes && apiRes.status == API_CODE.Succeed) {
        if(apiRes.data.records.length > 0){
          try {
            this.id = apiRes.data.records[0].id
            this.items = JSON.parse(apiRes.data.records[0].value)
          } catch (error) {
            this.items = []
          }
          
        }
      }
    },
    removeCat(index) {
      this.items.splice(index,1)
    },
    async getCategory(){
      const apiRes = await ProductCategoryService.get({})
      if (apiRes && apiRes.status == API_CODE.Succeed) {
        this.category = apiRes.data.records
      }
    },
    newCat(){
      if(!this.chooseNewCat){
        return
      }
      const chooseNewCat = this.chooseNewCat
      const newCat = this.configFieldArr.find(o => o.id == chooseNewCat);
      this.items.push({type:newCat.id})
    },
    async save(){
      let apiRes = false
      this.loading=true
      if(this.id){
        apiRes = await ConfigService.update({id:this.id,name:this.name,value:JSON.stringify(this.items)})
      }else{
        apiRes = await ConfigService.create({name:this.name,value:JSON.stringify(this.items)})
      }
      this.loading=false
      if (apiRes && apiRes.status == API_CODE.Succeed) {
        this.id = apiRes.data.id
        JsCoreHelper.showErrorMsg('Lưu thành công','info');
      }else{
        JsCoreHelper.showErrorMsg(apiRes ? apiRes?.data?.message : 'Có lỗi xảy ra');
      }
    },
    changeCat(index){
      const chooseCat = this.category.find(o => o.id == this.items[index].id)
      this.items[index].name = chooseCat.name;
      this.items[index].limit = this.items[index].limit ? this.items[index].limit : 4;
    }

  },
}
</script>
