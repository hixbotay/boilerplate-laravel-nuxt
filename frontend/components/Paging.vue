<template>
    <div class='fvn-paging'>
      <v-pagination v-model="page_component" :length="totalPage" :total-visible="7" @first="first" @last="last" @next="next" @prev="prev"></v-pagination>
    </div>
</template>
 
<script>
export default {
    name: 'Paging',
    props: {
      totalPage:{
        type: Number,
        default: 0
      },
      page:{
        type: Number,
        default: 1
      },
    },
    data() {
      return {
        page_component: this.page
      }
    },
    computed: {
      
    },
    methods: {
      first(){
        this.$emit('first');
      },
      last(){
        this.$emit('last');
      },
      next(){
        this.$emit('next');
      },
      prev(){
        this.$emit('prev');
      },
      changePage(){
        console.log('page_component',this.page_component)
        const page = this.page_component
        this.$emit('changePage',this.page_component);
      },
    },
    watch:{
      page_component(){
        this.changePage()
      }
    }
};
</script>