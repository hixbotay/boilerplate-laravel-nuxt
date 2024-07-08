<template>
    <v-row class="product">
        <v-col class="text-center">
            <v-col class="text-left">
                <h2 class="title-page-sub">Danh mục sản phẩm</h2>
                <v-card>
                    <v-card-title>
                        <v-spacer></v-spacer>
                        <v-text-field v-model="query.keyword" append-icon="mdi-magnify" label="Tìm kiếm" single-line
                            hide-details></v-text-field>
                        <v-btn color="primary" dark class="" @click="initilize()">Tìm kiếm</v-btn>
                    </v-card-title>
                    <v-toolbar>
                        <v-btn color="primary" dark class="mr-2" @click="editItem()">Thêm mới</v-btn>
                        <v-divider class="mx-4" inset vertical></v-divider>
                    </v-toolbar>
                    <v-simple-table>
                        <template v-slot:default>
                            <thead>
                                <tr>
                                    <th class="text-left">
                                        Danh mục
                                        <v-btn
                                            v-show="!isOrderBy('name', 'DESC')"
                                            variant="text"
                                            icon=""
                                            @click="setOrderBy('name', 'DESC')">
                                            <v-icon size="x-large">mdi-menu-up</v-icon>
                                        </v-btn>
                                        <v-btn
                                            v-show="isOrderBy('name', 'DESC')"
                                            variant="text"
                                            icon=""
                                            @click="setOrderBy('name', 'ASC')">
                                            <v-icon size="x-large">mdi-menu-down</v-icon>
                                        </v-btn>
                                    </th>
                                    <th class="text-left">Thông tin</th>
                                    <th class="text-left">Hình ảnh</th>
                                    <th class="text-left">Trạng thái</th>
                                    <th class="text-left">
                                        Ngày tạo
                                        <v-btn
                                            v-show="!isOrderBy('created_at', 'DESC')"
                                            variant="text"
                                            icon=""
                                            @click="setOrderBy('created_at', 'DESC')">
                                            <v-icon size="x-large">mdi-menu-up</v-icon>
                                        </v-btn>
                                        <v-btn
                                            v-show="isOrderBy('created_at', 'DESC')"
                                            variant="text"
                                            icon=""
                                            @click="setOrderBy('created_at', 'ASC')">
                                            <v-icon size="x-large">mdi-menu-down</v-icon>
                                        </v-btn>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(item, index) in items">
                                    <tr>
                                        <td>{{ item.name }}</td>
                                        <td>{{ item.description }}</td>
                                        <td><img v-if="item.image" :src="getImage(item.image)" class="product-image" /></td>
                                        <td>{{ getStatus(item.status) }}</td>
                                        <td>{{ item.created_at }}</td>
                                        <td>
                                            <!-- {{ getChildCat(item.child) }}  -->
                                            <v-icon size="small" class="me-2" @click="editItem(index)"> mdi-pencil </v-icon>
                                            <v-icon size="small" @click="deleteItem(index)"> mdi-delete </v-icon>
                                        </td>
                                    </tr>
                                    <template v-if="getChildCat(item.child)">
                                        <tr v-for="(child, i) in item.child" class="cat__children">
                                            <td>---- {{ child.name }}</td>
                                            <td>{{ child.description }}</td>
                                            <td><img v-if="child.image" :src="getImage(child.image)" class="product-image" />
                                            </td>
                                            <td>{{ getStatus(child.status) }}</td>
                                            <td>{{ child.created_at }}</td>
                                            <td>
                                                <!-- {{ getChildCat(item.child) }}  -->
                                                <v-icon size="small" class="me-2" @click="editItem(index,i)"> mdi-pencil </v-icon>
                                                <v-icon size="small" @click="deleteItem(index,i)"> mdi-delete </v-icon>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <v-pagination v-model="query.page" :length="totalPage" :total-visible="7"></v-pagination>

                    <v-dialog v-model="dialog">
                        <v-card>
                            <v-card-title>
                                <span class="text-h5">{{ formTitle }}</span>
                            </v-card-title>
                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field v-model="editedItem.name" label="Tên danh mục"></v-text-field>
                                            <p v-if="error && error.name" class="text-error">{{ error.name }}</p>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-select v-model="editedItem.parent_id" :items="listCategories"
                                                item-text="name" item-value="id" label="Danh mục cha"></v-select>
                                            <p v-if="error && error.parent_id" class="text-error">{{ error.parent_id }}</p>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-file-input v-model="editedItem.image" label="Ảnh"
                                                name="image"></v-file-input>
                                            </v-col>
                                            <v-col cols="12" sm="12" md="12">
                                                <label for="">Thông tin</label>
                                            <Editor :content="editedItem.description"
                                                @changeContent="editedItem.description = $event" />
                                            <p v-if="error && error.description" class="text-error">{{ error.description }}
                                            </p>
                                        </v-col>
                                        <v-row v-if="inputDisable">
                                            <p><img v-if="editedItem.image" :src="getImage(editedItem.image)"
                                                    class="w-100 d-block" /></p>
                                            <v-col cols="12">
                                                <p>Ngày tạo {{ editedItem.created_at }}</p>
                                                <p>Ngày cập nhật {{ editedItem.updated_at }}</p>
                                            </v-col>
                                        </v-row>
                                    </v-row>
                                </v-container>
                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue-darken-1" variant="text" @click="close">
                                    Cancel
                                </v-btn>
                                <v-btn color="blue-darken-1" :disabled="loading" variant="text" @click="save">
                                    Save
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="dialogDelete" max-width="500px">
                        <v-card>
                            <v-card-title class="text-h5">Bạn có muốn xóa {{ editedItem.name }}?</v-card-title>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Cancel</v-btn>
                                <v-btn color="blue-darken-1" variant="text" @click="deleteItemConfirm">OK</v-btn>
                                <v-spacer></v-spacer>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </v-card>
            </v-col>
        </v-col>
    </v-row>
</template>
  
<script>
import ProductCategoryService from '~/service/ProductCategoryService'
import CommonUtil from '~/utils/CommonUtil';
import Editor from '~/components/Editor.vue'

import JsCoreHelper from '~/utils/JsCoreHelper';
import { API_CODE } from '~/utils/const';

export default {
    name: 'Category',
    components: { Editor },
    layout: 'admin',
    data: () => ({
        dialog: false,
        loading: false,
        selected: [],
        middleware: ['auth'],
        dialogDelete: false,
        query: {
            keyword: '',
            page: 1,
            per_page: 50
        },
        error: [],
        totalPage: 1,
        items: [],
        editedIndex: -1,
        editedItem: {},
        defaultItem: {},
        listCategories: []
    }),

    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Thêm mới' : 'Chính sửa'
        },
        inputDisable() {
            return !!this.editedItem.id;
        }
    },
    watch: {
        'query.page'(_newVal, _oldVal) {
            this.getData()
        },
        'query.per_page'(_newVal, _oldVal) {
            this.getData()
            this.getPaging()
        }
    },

    created() {
        this.initilize();
    },

    methods: {
        initilize() {
            this.getData();
            this.getPaging();
        },
        async getData() {
            const categoryRes = await ProductCategoryService.getTreeCategory(this.query)
            if (categoryRes && categoryRes.status == API_CODE.Succeed) {
                this.items = categoryRes.data.records;
                this.getListCategories();
            }
        },
        async getPaging() {
            const queryPaging = { ...this.query }
            queryPaging.is_paginate = 1
            const categoryRes = await ProductCategoryService.getTreeCategory(queryPaging);
            if (categoryRes && categoryRes.status == API_CODE.Succeed) {
                this.totalPage = Math.ceil(categoryRes.data.total_records / this.query.per_page)
            }
        },

        editItem(index, parent = -1) {
            // eslint-disable-next-line eqeqeq
            if (index != undefined) {
                if (parent >= 0) {
                    this.editedItem = this.items[index].child[parent];
                } else {
                    this.editedItem = this.items[index];
                    this.getListCategories(index);
                }
                this.editedIndex = index;
            }
            this.dialog = true;
        },

        deleteItem(index, parent = -1) {
            // eslint-disable-next-line eqeqeq
            if (index != undefined) {
                if (parent >= 0) {
                    this.editedItem = this.items[index].child[parent];
                } else {
                    this.editedItem = this.items[index];
                }
                this.editedIndex = index;
                this.dialogDelete = true;
            }
        },

        async deleteItemConfirm() {
            const apiRes = await ProductCategoryService.destroy(this.editedItem.id)
            if (apiRes && apiRes.status == API_CODE.DeleteSucceed) {
                this.getData()
            }
            this.closeDelete();
        },

        close() {
            this.loading = false;
            this.dialog = false;
            this.getListCategories();
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },

        closeDelete() {
            this.dialogDelete = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },
        setOrderBy(orderBy, orderType) {
            this.query.order_by = orderBy;
            this.query.order_type = orderType;
            this.getData();
        },
        isOrderBy(orderBy, orderType) {
            if (this.query.order_by == orderBy && this.query.order_type == orderType) {
            return true;
            }
            return false;
        },
        async save() {
            this.loading = true;
            const formData = this.getFormData(this.editedItem);
            if (this.editedIndex > -1) {
                const saveRes = await ProductCategoryService.update(formData);
                if (saveRes && saveRes.status == API_CODE.Succeed) {
                    this.initilize();
                    this.close();
                } else {
                    if (saveRes && saveRes.data.errors) {
                        this.error = saveRes.data.errors
                    }
                    JsCoreHelper.showErrorMsg(saveRes.data.message);
                }
            } else {
                const saveRes = await ProductCategoryService.create(formData)
                if (saveRes && saveRes.status == API_CODE.Succeed) {
                    this.initilize();
                    this.close();
                } else {
                    if (saveRes && saveRes.data.errors) {
                        this.error = saveRes.data.errors
                    }
                    JsCoreHelper.showErrorMsg(saveRes.data.message);
                }
            }
        },
        getFormData(data) {
            const formData = new FormData();
            const arrayKey = Object.keys(data);
            for (const i in arrayKey) {
                const key = arrayKey[i]
                formData.append(key, data[key]);
            }
            return formData;
        },
        getStatus(statusNumber) {
            if (statusNumber === 1) {
                return 'Đang hoạt động';
            }
            if (statusNumber === 0) {
                return 'Đã ẩn';
            }
        },
        getChildCat(child) {
            if (child.length > 0) {
                return true;
            }
            return false;
        },

        getImage(image) {
            if (typeof image === 'string') {
                return CommonUtil.getImage(image)
            }
            return null;
        },
        getListCategories(index = -1) {
            if (index >= 0) {
                this.listCategories = this.items.map((item, i) => {
                    if (i == index) {
                        return null
                    } 
                    return {
                        name: item.name,
                        id: item.id
                    }
                });
            } else {
                this.listCategories = this.items.map((item) => {
                    return {
                        name: item.name,
                        id: item.id,
                    }
                });
            }
        }
    }
}
</script>
<style lang="scss" scoped>
.cat__children {
    background-color: #f6e7e7;
}</style>