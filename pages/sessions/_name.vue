<template>
  <section class="mt-5">
    <h2 class="container text-lowercase">session</h2>
    <div class="section-header-divider"></div>

    <div class="container">

      <div class="row">
        <div class="col-lg-3 my-5 pr-4 d-flex flex-column">
          <img :src='"~/assets/img/sessions/" + session.img + ".png"' class="img-fluid">
        </div>
        <div class="col-lg-9 mt-3 p-4">

         <div class="text-lowercase">
          <h1> {{ session.title }} </h1>
          <h4 class="body-style"> {{ session.day }}, {{ session.time }}, sala {{ session.place }}. </h4>
        </div>

        <nuxtent-body class="mt-4 lead" :body="session.body" />

        <h4><a class="btn btn-lg btn-primary " :href='session.tickets'>Inscrições</a></h4>
      </div>
    </div>

  </div>
</section>
</template>

<script>
  export default {

    head () {
      return {
        title: this.session.title + " | semana de informatica",
      }
    },

    async asyncData({ app, route }) {
      return {
        session: await app.$content("/").get(route.path)
      }
    },

    name: 'Session',

    data () {
      return {

      }
    }
  }
</script>

<style lang="css" scoped>
.text-strong {
  font-weight: 600;
}
</style>