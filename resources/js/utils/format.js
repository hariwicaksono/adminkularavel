// src/plugins/helpers.js
import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')

const helpers = {
  formatDate(value, format = 'DD MMM YYYY HH:mm') {
    return value ? dayjs(value).format(format) : '-'
  },
  formatCurrency(value, locale = 'id-ID', currency = 'IDR') {
    if (value == null) return '-'
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency
    }).format(value)
  },
  capitalize(text) {
    if (!text) return ''
    return text.charAt(0).toUpperCase() + text.slice(1)
  }
}

export default {
  install(app) {
    app.config.globalProperties.$helpers = helpers
  }
}
