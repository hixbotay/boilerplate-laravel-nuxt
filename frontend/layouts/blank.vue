<template>
  <v-app
    id="blank"
    :dark="isDark"
    >
    <SeoHead />
    <v-main>
      <v-container>
        <Nuxt />
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { mapState } from 'vuex'
import SeoHead from '~/components/SeoHead'

export default {
  name: 'LayoutBlank',
  components: { SeoHead },
  data() {
    return {
      title: 'Nuxtify'
    }
  },
  computed: {
    ...mapState({ isDark: (state) => state.core.theme.isDark })
  }
}
</script>

<!-- Styles -->
<style lang="scss" scoped>
@import '~/assets/scss/theme';
#blank {
  background: linear-gradient(to bottom, #1976d2, #0d47a1); 
}
</style>
