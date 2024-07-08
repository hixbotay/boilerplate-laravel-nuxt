<template>
  <div class="d-flex justify-center align-center min-h-100vh">
    <v-flex lg6>
      <v-card elevation="2" light class="bg-wave">
        <v-card-title> Forgot Password </v-card-title>
        <v-form v-model="valid" name="form-login" @submit="onSubmit()">
          <v-container>
            <v-row>
              <v-col cols="12" md="12">
                <v-text-field
                  v-model.trim="email"
                  type="text"
                  label="Insert your email"
                  outlined
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-btn type="submit" color="primary">Login</v-btn>
              </v-col>
              <v-col cols="12" md="6" class="text-right">
                <nuxt-link to="/login">Sign Up</nuxt-link>
                or
                <nuxt-link to="/register">Sign In</nuxt-link>
              </v-col>
            </v-row>
          </v-container>
        </v-form>
      </v-card>
    </v-flex>
  </div>
</template>

<script>
export default {
  layout: 'blank',
  data() {
    return {
      email: '',
      password: '',
	  valid: false
    }
  },
  methods: {
    onSubmit() {
      return console.log('submit')
    },
    onSubmitSuccess() {
      return console.log('success')
    },
    onSubmitError() {
      return console.log('error')
    }
  }
}
</script>
