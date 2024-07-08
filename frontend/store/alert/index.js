const state = () => ({
  list: []
})

const getters = {
  getAlert(state){
    return state.alert
  }
}

const mutations = {
  add(state, alert){
    if(!alert.type){
      alert.type='info'
    }
    state.list.push(alert)
  },
  toggle(state){
    state.list.splice(0, 1);
  }
}

const actions = {
  async sampleFunction({ state, data }) {
    state.alert = data;
    return state.alert;
  }
}

/* Export all stores */
export default {
  state,
  mutations,
  getters,
  actions
}
