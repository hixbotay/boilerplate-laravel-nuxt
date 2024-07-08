<template>
  <section class="container">
    <client-only>
      <quill-editor v-if="!toggleEditor" ref="myQuillEditor" :options="editorOption" class="editor-form"
        @blur="onEditorBlur($event)" @focus="onEditorFocus($event)" @ready="onEditorReady($event)"
        :value="value"
        @change="handleChange"
      />
      <div v-if="toggleEditor">
        <v-textarea :value="value" @input="handleChangeText" full-width solo></v-textarea>
      </div>
      <v-btn small @click="toggleEditor=!toggleEditor">Toggle Editor</v-btn>
    </client-only>
  </section>
</template>

<script>
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'
import FileService from '~/service/FileService'
import { API_CODE } from '~/utils/const'
import JsCoreHelper from '~/utils/JsCoreHelper'
export default {
  name: 'quill-example-nuxt',
  // eslint-disable-next-line vue/require-prop-types
  props: {
    value: {
      type: String,
      default: ''
    },
  },
  data() {
    return {
      toggleEditor: false,
      editorOption: {
        // Some Quill options...
        placeholder: '',
        theme: 'snow',
        modules: {
          toolbar: {
            container: [
              ['bold', 'italic', 'underline', 'strike'], ['blockquote', 'code-block'],
              [{ 'list': 'ordered' }, { 'list': 'bullet' }],
              // [{ 'script': 'sub'}, { 'script': 'super' }],
              [{ 'indent': '-1'}, { 'indent': '+1' }], 
              // [{ 'direction': 'rtl' }],  
              [{ 'size': ['small', false, 'large', 'huge'] }],
              [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
              [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
              [{ 'font': [] }],
              [{ 'align': [] }],
              ['link'],
              ['formula'],
              ['clean'],  
              ['image'],
              // ['toggle'],
            ],
            handlers: {
                image: this.imageHandler
            }
          }
        }
      }
    }
  },
  mounted() {
  },
  methods: {
    onEditorBlur(editor) {
      // console.log('editor blur!', editor)
    },
    onEditorFocus(editor) {
      // console.log('editor focus!', editor)
    },
    onEditorReady(editor) {
      // console.log(this.value)
      // console.log('editor ready!', editor)
    },
    handleChange(e) {
      this.$emit('input', e.html)
    },
    handleChangeText(e) {
      this.$emit('input', e)
    },
    imageHandler(e) {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();
        input.onchange = async function() {
          const file = input.files[0];
          let quill = this.$refs.myQuillEditor.quill
          const range = quill.getSelection();
          let formData = new FormData();
          formData.append('path','public')
          formData.append('file',file)
          const resApi = await FileService.create(formData)
          if(resApi && resApi.status==API_CODE.Succeed){
            quill.insertEmbed(range.index, 'image', resApi.data.path); 
          }else{
            if(resApi){
              JsCoreHelper.showErrorMsg(resApi.data.message)
            }else{
              JsCoreHelper.showErrorMsg('Upload failed')
            }
          }
          return
        }.bind(this);
    },
  }
}
</script>

<style lang="scss">
.quill-editor {
  min-height: 500px;
  overflow-y: auto;
  height: 100%;
  width: 100%;
  .ql-container{
    height: 500px;
    overflow: auto;
  }
  .ql-editor{
    height: 500px;
    overflow: auto;
  }
}
</style>