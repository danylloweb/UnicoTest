<!-- ProductList.vue -->
<template>
  <div class="v-container">
    <v-card
      class="mx-auto"
      color="grey-lighten-3"
      max-width="600px"
    >
      <v-card-text>
        <v-text-field
          :loading="loading"
          density="compact"
          variant="solo"
          label="Pesquisar"
          append-inner-icon="mdi-magnify"
          single-line
          hide-details
          v-model="search"
          @click:append-inner="getSearch"
        ></v-text-field>
      </v-card-text>
    </v-card>
    <v-data-table
      :headers="headers"
      :items="products"
      :items-per-page="itemsPerPage"
      :page.sync="currentPage"
      :loading="isLoadingTable"
      loading-text="Carregando lista..."
      :sort-by="[{ key: 'id', order: 'asc' }]"
    >

      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Produtos</v-toolbar-title>

          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ props }">
              <v-btn
                color="primary"
                class="mb-2"
                v-bind="props"
              >
                Novo
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="text-h5">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" sm="6" md="12">
                      <v-text-field
                        v-model="editedItem.name"
                        label="Nome"
                        required
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="12">
                      <v-text-field
                        v-model="editedItem.description"
                        label="Descrição"
                        required
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="6">
                      <v-text-field
                        v-model="editedItem.price"
                        label="Preço"
                        required
                        @keyup="formatReal"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="6">
                      <v-select
                        v-model="editedItem.status_title"
                        label="Status"
                        required
                        :items="['Ativo', 'Inativo']"
                      ></v-select>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="close">
                  Cancelar
                </v-btn>
                <v-btn color="blue-darken-1" variant="text" @click="save">
                  Salvar
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">tem certeza que deseja remover produto?</v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Cancelar</v-btn>
                <v-btn color="blue-darken-1" variant="text" @click="deleteItemConfirm">OK</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon
          size="small"
          class="me-2"
          @click="editItem(item)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          size="small"
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
      </template>
      <template v-slot:no-data>
        <v-btn
          color="primary"
          @click="initialize"
        >
          Reset
        </v-btn>
      </template>
    </v-data-table>
  </div>

</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      products: [],
      currentPage: 0,
      numberOfPages: 0,
      itemsPerPage: 10,
      totalOfRows: 0,
      pageFrom: 0,
      pageTo: 0,
      isLoadingTable: true,
      search: null,
      sortSelected: '',
      dialog: false,
      dialogDelete: false,
      item_id:0,
      service_url: 'http://localhost/',
      headers: [
        { title: 'id', align: 'start', sortable: false, key: 'id'},
        { title: 'Nome', key: 'name' },
        { title: 'Descrição', key: 'description' },
        { title: 'Preço R$', key: 'price_formated' },
        { title: 'Status', key: 'status_title' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      editedIndex: -1,
      editedItem: {
        name: '',
        description: '',
        price: 0,
        status_title: '',
      },
      defaultItem: {
        name: '',
        description: '',
        price: 0,
        status_title: 'Ativo',
      },
    };
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'Novo Produto' : 'Editar Produto'
    },
  },

  watch: {
    dialog (val) {
      val || this.close()
    },
    dialogDelete (val) {
      val || this.closeDelete()
    },
  },
  mounted() {
    this.loadProducts(1, 10, this.search);
  },
  methods: {
    loadProducts(page = 1, limit = 10, search = null) {
      let url = `${this.service_url}products?page=${page}&orderBy=id&sortedBy=asc&limit=${limit}`;
      if (search) {
        url = url + `&search=${search}`
      }
      axios.get(url)
        .then(response => {
          this.products = response.data.data;
          this.currentPage = response.data.meta.pagination.current_page;
          this.numberOfPages = response.data.meta.pagination.total_pages;
          this.itemsPerPage = response.data.meta.pagination.per_page;
          this.totalOfRows = response.data.meta.pagination.total;
          this.isLoadingTable = false;

        })
        .catch(error => {
          console.error('Erro ao carregar produtos', error);
        });
    },
    getSearch() {
      this.isLoadingTable = true;
      this.loadProducts(this.currentPage, this.itemsPerPage, this.search)
    },

    deleteProduct(id) {
      axios.delete(`${this.service_url}products/${id}`)
        .then(response => {
          this.loadProducts();
        })
        .catch(error => {
          console.error('Erro ao excluir produto', error);
        });
    },
    editItem (item) {
      this.editedIndex = this.products.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },

    deleteItem (item) {
      this.item_id = item.id;
      this.editedIndex = this.products.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },

    deleteItemConfirm () {
      this.products.splice(this.editedIndex, 1)
      this.closeDelete()
      this.deleteProduct(this.item_id);
    },

    close () {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    closeDelete () {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    save () {
      if (this.editedIndex > -1) {
        axios.put(`${this.service_url}products/${this.editedItem.id}`, this.editedItem)
          .then(response => {
            this.loadProducts();
          })
          .catch(error => {
            console.error('Erro ao atualizar produto', error);
            alert(error.message)
          });
      } else {
        axios.post(`${this.service_url}products/`, this.editedItem)
          .then(response => {
            this.loadProducts();
          })
          .catch(error => {
            alert(error.message)
          });
      }
      this.close()
    },
    formatReal() {
     let valor = this.editedItem.price + '';

      valor = parseInt(valor.replace(/[\D]+/g, ''));
      valor = valor + '';
      valor = valor.replace(/([0-9]{2})$/g, ",$1");

      if (valor.length > 6) {
        valor = valor.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
      }

      this.editedItem.price = valor;
      if(valor == 'NaN') this.editedItem.price = '';
    }
  },
};
</script>
