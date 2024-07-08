<template>
  <div class="category-list">
    <v-card>
      <v-card-title>
        <span class="text-h5">Danh mục</span>
      </v-card-title>
      <v-card-text>
        <div class="category">
          <div v-for="(item, index) in categories" :key="item.id" class="d-flex category-item">
              <v-checkbox v-model="selected" :value="item.id" @click="updateCat()"/>
              <div class="cat-name">{{ item.name }}
                <template v-if="item.child && item.child.length>0">
                      <div v-if="item.child.length>0" v-for="(child, indexchild) in item.child" :key="indexchild" class="d-flex category-item-child">
                        <v-checkbox v-model="selected" :value="child.id" @click="updateCat()"/>
                        <div class="cat-name">{{ child.name }}</div>
                      </div>
                  </template>
              </div>
            </div>
        </div>

        <div class="d-flex">
          <div>
            <v-text-field v-model="newCatData.name" label="Danh mục mới"></v-text-field>
            <v-select v-model="newCatData.parent_id" :items="categories" item-text="name" item-value="id"
                          label="Danh mục cha" :clearable="true"></v-select>
          </div>
          <v-btn icon fab small @click="addNew()" class="mt-2">
            <v-icon left>mdi-plus</v-icon>
          </v-btn>
        </div>
      </v-card-text>

    </v-card>
  </div>
</template>

<script>

import { API_CODE } from '~/utils/const';

export default {
  name: 'Category',
  props: {
    inputSelected: { type: Array },
    categories: { type: Array },
  },
  data() {
    return {
      selected: [],
      newCatData: {
        name: '',
        parent_id: 0
      },
      items: []
    }
  },
  mounted(){
    this.selected = this.inputSelected
  },
  methods: {
    async addNew() {
      if (this.newCatData.name.trim == '') {
        return
      }
      this.$emit('new-category', this.newCatData);
      this.newCatData = {
          name: '',
          parent_id: 0
        }
    },
    checkAll() {
      try {
        if (this.selected.length == this.items.length) {
            this.selected = []
          } else {
            this.selected = this.items.map(function (e) { return e.id })
          }
      } catch (error) {
        console.log('select error '+error)
        this.selected = []
      }
      this.updateCat()
      
    },
    updateCat(){
      this.$emit('update-category', this.selected);
    }
  },
  watch:{
    inputSelected(){
      this.selected = this.inputSelected
    }
  }
}
</script>
