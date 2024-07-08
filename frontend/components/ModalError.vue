<template>
  <div class="alert-area">
    <v-alert v-for="(alert,index) in alerts" :type="alert.type" elevation="24" shaped :key="index" closable>{{ alert.message }}</v-alert>
  </div>

</template>

<script>
export default {
  name: 'ModalError',
  props: {
  },
  computed: {
    alerts () {
      return this.$store.state.alert.list
    }
  },
  data() {
    return {
    };
  },
  created(){

  },
  methods: {
  }
}
</script>
