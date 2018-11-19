import Vue from 'vue'
import VueTippy from 'vue-tippy'
import Devise from './Devise'
import Administration from './components/admin/Administration'
import ActionBar from './components/utilities/ActionBar'
import Sidebar from './components/utilities/Sidebar'
import Help from './components/utilities/Help'
import DeviseImg from './components/utilities/images/DeviseImage'
import SliceEditor from './components/pages/slices/SliceEditor'
import Slices from './Slices'
import DeviseStore from './vuex/store'
import PortalVue from 'portal-vue'
import { DeviseBus } from './event-bus.js'
import routes from './router/route.config.js'
import alertConfirm from './directives/alert-confirm'
import Tilt from './directives/tilt'
import Tuck from './directives/tuck'

import EditPage from './components/pages/Editor'

Vue.config.productionTip = false

import { mapGetters } from 'vuex'

const DevisePlugin = {
  install (Vue, { store, router, bus, options }) {
    

    if (typeof store === 'undefined') {
      throw new Error('Please provide a vuex store.')
    }

    if (typeof router === 'undefined') {
      throw new Error('Please provide a vue router.')
    }

    // Set the devise route to Edit Page for any application routes
    // that aren't setup to take over the admin. This allows us to see the
    // page editor even if you are navigating around the application routes.
    router.options.routes.map(route => {
      if (!route.hasOwnProperty('components')) {
        route.components = {}
      }
      if (!route.components.hasOwnProperty('devise')) {
        route.components.devise = EditPage
      }
    })

    for (const route in routes) {
      if (routes.hasOwnProperty(route)) {
        const routeToCheck = routes[route];
        var canAdd = true

        for (const customRoute in router.options.routes) {
          if (router.options.routes.hasOwnProperty(customRoute)) {
            const routeToCheckAgainst = router.options.routes[customRoute];
            if (routeToCheckAgainst.name === routeToCheck.name) {
              canAdd = false
            }
          }
        }

        if (canAdd) {
          router.addRoutes([routeToCheck])
        }
      }
    }

    if (typeof deviseSettings === 'undefined') {
      window.deviseSettings = function () {};
    }

    // If the bus isn't set we'll use our own bus
    if (typeof bus === 'undefined') {
      deviseSettings.__proto__.$bus = DeviseBus
    } else {
      deviseSettings.__proto__.$bus = bus
    }

    // Tooltips
    Vue.use(VueTippy)

    // Portals to render items outside of their component
    Vue.use(PortalVue)

    // Register global components
    Vue.component('Devise', Devise)
    Vue.component('DeviseImg', DeviseImg)
    Vue.component('Help', Help)
    Vue.component('Slices', Slices)
    Vue.component('SliceEditor', SliceEditor)
    Vue.component('Administration', Administration)
    Vue.component('ActionBar', ActionBar)
    Vue.component('Sidebar', Sidebar)

    if (typeof store.state.adminMenu !== 'undefined') {
      DeviseStore.state.adminMenu = Object.assign({}, store.state.adminMenu)
    }

    if (typeof store.state.settingsMenu !== 'undefined') {
      DeviseStore.state.settingsMenu = Object.assign({}, store.state.settingsMenu)
    }

    // Setup the currentPage and Sites arrays in the store b/c they are necessary in 
    // some apps to be ready to go right away
    DeviseStore.state.currentPage = Object.assign({}, DeviseStore.state.currentPage, deviseSettings.$page)
    DeviseStore.state.sites = Object.assign({}, DeviseStore.state.sites, {data: deviseSettings.$sites})

    // Register devise vuex module and sync it with the store
    store.registerModule('devise', DeviseStore)

    // Register alert / confirm directive
    Vue.directive('devise-alert-confirm', alertConfirm)

    // Register tilt directive
    Vue.directive('tilt', Tilt)

    // Register tuck directive
    Vue.directive('tuck', Tuck)

    let deviseOptions = Object.assign({
      breakpoints: {
        mobile: 575,
        tablet: 768,
        desktop: 991,
        largeDesktop: 1199
      }
    }, options)

    // We call Vue.mixin() here to inject functionality into all components.
    Vue.mixin({
      data () {
        return {
          deviseOptions: deviseOptions,
          tippyConfiguration: {
            interactive: true,
            animation: 'shift-toward',
            arrow: true,
            inertia: true,
            interactiveBorder: 20,
            maxWidth: '300px',
            theme: 'devise',
            trigger: 'mouseenter focus'
          }
        }
      },
      methods: {
        // Convienience method to push things into the router from templates
        goToPage (pageName, params) {
          this.$router.push({name: pageName, params: params})
        },
        href (url) {
          window.open(url, '_self')
        },
        launchMediaManager (callbackObject, callbackProperty) {
          deviseSettings.$bus.$emit('devise-launch-media-manager', {
            callback: function (media) {
              callbackObject[callbackProperty] = media.url
            }
          })
        },
        can(permission) {
          let toCheck = (!Array.isArray(permission)) ? [permission] : permission;
          let allowed = deviseSettings.$user.permissions_list ? deviseSettings.$user.permissions_list : []
          for (let i = 0; i < toCheck.length; i++) {
            let found = allowed.find(function(perm) {
              return perm === toCheck[i];
            });

            if(found) return true;
          }
        }
      },
      computed: {
        ...mapGetters('devise', [
          'breakpoint',
          'currentPage',
          'currentUser',
          'lang',
          'theme'
        ])
      },
      // This sets a prop to be accepted by all components in a custom Vue
      // app that resides within Devise. Makes it a little easier to pass
      // down any data to custom child components
      props: ['devise', 'slices', 'models'],
      store: store
    })

    if (typeof deviseSettings.$mothership !== 'undefined' && deviseSettings.$mothership !== null) {
      store.commit('devise/setMothership', deviseSettings.$mothership)
    }
  }
}

export default DevisePlugin
