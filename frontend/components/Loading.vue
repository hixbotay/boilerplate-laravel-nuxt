<template>
    <div>
      <div v-show="loading" class='loader'>
        <div class="loader-logo">
            loading...
        </div>
    </div>
    <div v-show="loading==false" ><slot /></div>
    </div>
</template>
 
<script>
import JsCoreHelper from '~/utils/JsCoreHelper.js';
export default {
    name: 'Loading',
    props: {
      loading: {}
    },
    data() {
      return {
      }
    },
    computed: {
      nameState() {
      }
    },
    methods: {
    }
};
</script>