import dayjs from 'dayjs/esm'
import advancedFormat from 'dayjs/plugin/advancedFormat'
import customParseFormat from 'dayjs/plugin/customParseFormat'
import localeData from 'dayjs/plugin/localeData'
import timezone from 'dayjs/plugin/timezone'
import utc from 'dayjs/plugin/utc'
import flatpickr from 'flatpickr'
import flatpickrLocales from "flatpickr/dist/l10n"

import ConfirmDate from "flatpickr/dist/esm/plugins/confirmDate/confirmDate.js"
import MonthSelect from "flatpickr/dist/esm/plugins/monthSelect/index.js"
import WeekSelect from "flatpickr/dist/esm/plugins/weekSelect/weekSelect.js"
// import rangePlugin from 'flatpickr/dist/plugins/rangePlugin.js'

dayjs.extend(advancedFormat)
dayjs.extend(customParseFormat)
dayjs.extend(localeData)
dayjs.extend(timezone)
dayjs.extend(utc)

window.dayjs = dayjs

export default function flatpickrComponent(state, attrs) {
  const timezone = dayjs.tz.guess()

  return {
    state,
    attrs,
    timezone,

    locale: String(attrs.locale),

    fp: null,

    init: function() {
      console.log('INit flatpickr', this.state, this.attrs)
      Livewire.on('attributes-updated', (props) => {
        console.log('Attributes updated', props)
      })
      const customLocale = flatpickrLocales[this.locale] ?? flatpickrLocales['en'];
      const plugins = [
        // new ConfirmDate({
        //   showAlways: false,
        // }),
      ];
      if (this.attrs.monthPicker) {
        plugins.push(new MonthSelect({
          shorthand: this.attrs.monthPickerShorthand || false,
          dateFormat: this.attrs.dateFormat || 'Y-m',
          altFormat: this.attrs.altFormat || 'F Y',
        }))
      } else if (this.attrs.weekPicker) {
        plugins.push(new WeekSelect({
        }))
      }
      if (this.attrs.rangePicker) {
        // plugins.push(new rangePlugin({
        // }))
      }
      const config = {
        disableMobile: true,
        initialDate: this.state,
        defaultDate: this.state,
        static: false,
        altInput: true,
        ...this.attrs,
        plugins,
      }
      dayjs.locale(locales[this.locale] ?? locales['en'])
      flatpickr.localize(customLocale)
      this.fp = flatpickr(this.$refs.input, config);
      this.fp.parseDate(this.state, this.fp.config.dateFormat);
    },
    updateAttributes(attrs) {
        console.log('Update attributes', attrs)
    }
  }
}

const locales = {
  ar: require('dayjs/locale/ar'),
  bs: require('dayjs/locale/bs'),
  ca: require('dayjs/locale/ca'),
  ckb: require('dayjs/locale/ku'),
  cs: require('dayjs/locale/cs'),
  cy: require('dayjs/locale/cy'),
  da: require('dayjs/locale/da'),
  de: require('dayjs/locale/de'),
  en: require('dayjs/locale/en'),
  es: require('dayjs/locale/es'),
  et: require('dayjs/locale/et'),
  fa: require('dayjs/locale/fa'),
  fi: require('dayjs/locale/fi'),
  fr: require('dayjs/locale/fr'),
  hi: require('dayjs/locale/hi'),
  hu: require('dayjs/locale/hu'),
  hy: require('dayjs/locale/hy-am'),
  id: require('dayjs/locale/id'),
  it: require('dayjs/locale/it'),
  ja: require('dayjs/locale/ja'),
  ka: require('dayjs/locale/ka'),
  km: require('dayjs/locale/km'),
  ku: require('dayjs/locale/ku'),
  lt: require('dayjs/locale/lt'),
  lv: require('dayjs/locale/lv'),
  ms: require('dayjs/locale/ms'),
  my: require('dayjs/locale/my'),
  nl: require('dayjs/locale/nl'),
  no: require('dayjs/locale/nb'),
  pl: require('dayjs/locale/pl'),
  pt_BR: require('dayjs/locale/pt-br'),
  pt_PT: require('dayjs/locale/pt'),
  ro: require('dayjs/locale/ro'),
  ru: require('dayjs/locale/ru'),
  sv: require('dayjs/locale/sv'),
  th: require('dayjs/locale/th'),
  tr: require('dayjs/locale/tr'),
  uk: require('dayjs/locale/uk'),
  vi: require('dayjs/locale/vi'),
  zh_CN: require('dayjs/locale/zh-cn'),
  zh_TW: require('dayjs/locale/zh-tw'),
}
