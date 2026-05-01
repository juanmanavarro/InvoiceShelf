<template>
  <div class="graph-container h-[300px]">
    <canvas id="graph" ref="graph" />
  </div>
</template>

<script setup>
import { Chart } from 'chart.js/auto'
import {
  ref,
  reactive,
  computed,
  onMounted,
  onBeforeUnmount,
  watchEffect,
  inject,
} from 'vue'
import { useCompanyStore } from '@/scripts/admin/stores/company'

const utils = inject('utils')

const props = defineProps({
  labels: {
    type: Array,
    require: true,
    default: Array,
  },
  values: {
    type: Array,
    require: true,
    default: Array,
  },
  invoices: {
    type: Array,
    require: true,
    default: Array,
  },
  expenses: {
    type: Array,
    require: true,
    default: Array,
  },
  receipts: {
    type: Array,
    require: true,
    default: Array,
  },
  income: {
    type: Array,
    require: true,
    default: Array,
  },
})

let myLineChart = null
const graph = ref(null)
const companyStore = useCompanyStore()
const defaultCurrency = computed(() => {
  return companyStore.selectedCompanyCurrency
})

function isDarkModeEnabled() {
  return document.documentElement.classList.contains('dark')
}

function getChartTheme() {
  return isDarkModeEnabled()
    ? {
        tickColor: '#94a3b8',
        gridColor: 'rgba(71, 85, 105, 0.35)',
        tooltipBackground: '#0f172a',
        tooltipTitle: '#f8fafc',
        tooltipBody: '#e2e8f0',
        pointBackground: '#0f172a',
        salesColor: '#e2e8f0',
      }
    : {
        tickColor: '#64748b',
        gridColor: 'rgba(226, 232, 240, 1)',
        tooltipBackground: '#111827',
        tooltipTitle: '#f9fafb',
        tooltipBody: '#e5e7eb',
        pointBackground: '#ffffff',
        salesColor: '#040405',
      }
}

function applyThemeToChart() {
  if (!myLineChart) {
    return
  }

  const theme = getChartTheme()

  myLineChart.options.scales.x.ticks.color = theme.tickColor
  myLineChart.options.scales.x.grid.color = theme.gridColor
  myLineChart.options.scales.y.ticks.color = theme.tickColor
  myLineChart.options.scales.y.grid.color = theme.gridColor
  myLineChart.options.plugins.tooltip.backgroundColor = theme.tooltipBackground
  myLineChart.options.plugins.tooltip.titleColor = theme.tooltipTitle
  myLineChart.options.plugins.tooltip.bodyColor = theme.tooltipBody
  myLineChart.data.datasets[0].borderColor = theme.salesColor
  myLineChart.data.datasets[0].pointBorderColor = theme.salesColor
  myLineChart.data.datasets[0].pointHoverBackgroundColor = theme.salesColor

  myLineChart.data.datasets.forEach((dataset) => {
    dataset.pointBackgroundColor = theme.pointBackground
  })

  myLineChart.update('none')
}

watchEffect(() => {
  if (props.labels) {
    if (myLineChart) {
      myLineChart.reset()
      update()
    }
  }
})

onMounted(() => {
  let context = graph.value.getContext('2d')
  const theme = getChartTheme()
  let options = reactive({
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: {
        ticks: {
          color: theme.tickColor,
        },
        grid: {
          color: theme.gridColor,
        },
      },
      y: {
        ticks: {
          color: theme.tickColor,
        },
        grid: {
          color: theme.gridColor,
        },
      },
    },
    plugins: {
      tooltip: {
        enabled: true,
        backgroundColor: theme.tooltipBackground,
        titleColor: theme.tooltipTitle,
        bodyColor: theme.tooltipBody,
        callbacks: {
          label: function (context) {
            return utils.formatMoney(
              Math.round(context.parsed.y * 100),
              defaultCurrency.value
            )
          },
        },
      },
      legend: {
        display: false,
      },
    },
  })

  let data = reactive({
    labels: props.labels,
    datasets: [
      {
        label: 'Sales',
        fill: false,
        tension: 0.3,
        backgroundColor: 'rgba(230, 254, 249)',
        borderColor: theme.salesColor,
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderColor: theme.salesColor,
         pointBackgroundColor: theme.pointBackground,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBackgroundColor: theme.salesColor,
        pointHoverBorderColor: 'rgba(220,220,220,1)',
        pointHoverBorderWidth: 2,
        pointRadius: 4,
        pointHitRadius: 10,
        data: props.invoices.map((invoice) => invoice / 100),
      },
      {
        label: 'Receipts',
        fill: false,
        tension: 0.3,
        backgroundColor: 'rgba(230, 254, 249)',
        borderColor: 'rgb(2, 201, 156)',
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderColor: 'rgb(2, 201, 156)',
         pointBackgroundColor: theme.pointBackground,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBackgroundColor: 'rgb(2, 201, 156)',
        pointHoverBorderColor: 'rgba(220,220,220,1)',
        pointHoverBorderWidth: 2,
        pointRadius: 4,
        pointHitRadius: 10,
        data: props.receipts.map((receipt) => receipt / 100),
      },
      {
        label: 'Expenses',
        fill: false,
        tension: 0.3,
        backgroundColor: 'rgba(245, 235, 242)',
        borderColor: 'rgb(255,0,0)',
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderColor: 'rgb(255,0,0)',
         pointBackgroundColor: theme.pointBackground,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBackgroundColor: 'rgb(255,0,0)',
        pointHoverBorderColor: 'rgba(220,220,220,1)',
        pointHoverBorderWidth: 2,
        pointRadius: 4,
        pointHitRadius: 10,
        data: props.expenses.map((expense) => expense / 100),
      },
      {
        label: 'Net Income',
        fill: false,
        tension: 0.3,
        backgroundColor: 'rgba(236, 235, 249)',
        borderColor: 'rgba(88, 81, 216, 1)',
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderColor: 'rgba(88, 81, 216, 1)',
         pointBackgroundColor: theme.pointBackground,
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBackgroundColor: 'rgba(88, 81, 216, 1)',
        pointHoverBorderColor: 'rgba(220,220,220,1)',
        pointHoverBorderWidth: 2,
        pointRadius: 4,
        pointHitRadius: 10,
        data: props.income.map((_i) => _i / 100),
      },
    ],
  })

  myLineChart = new Chart(context, {
    type: 'line',
    data: data,
    options: options,
  })

  window.addEventListener('invoiceshelf-theme-changed', applyThemeToChart)
})

onBeforeUnmount(() => {
  window.removeEventListener('invoiceshelf-theme-changed', applyThemeToChart)

  if (myLineChart) {
    myLineChart.destroy()
    myLineChart = null
  }
})

function update() {
  myLineChart.data.labels = props.labels
  myLineChart.data.datasets[0].data = props.invoices.map(
    (invoice) => invoice / 100
  )
  myLineChart.data.datasets[1].data = props.receipts.map(
    (receipt) => receipt / 100
  )
  myLineChart.data.datasets[2].data = props.expenses.map(
    (expense) => expense / 100
  )
  myLineChart.data.datasets[3].data = props.income.map((_i) => _i / 100)
  myLineChart.update('none')
}
</script>
