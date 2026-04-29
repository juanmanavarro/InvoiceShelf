<template>
  <div class="grid grid-cols-12 gap-8 mt-6 mb-8">
    <BaseCustomerSelectPopup
      v-model="invoiceStore.newInvoice.customer"
      :valid="v.customer_id"
      :content-loading="isLoading"
      type="invoice"
      class="col-span-12 lg:col-span-5 pr-0"
    />

    <BaseInputGrid class="col-span-12 lg:col-span-7">
      <BaseInputGroup
        :label="$t('invoices.invoice_date')"
        :content-loading="isLoading"
        required
        :error="v.invoice_date.$error && v.invoice_date.$errors[0].$message"
      >
        <BaseDatePicker
          v-model="invoiceStore.newInvoice.invoice_date"
          :content-loading="isLoading"
          :calendar-button="true"
          calendar-button-icon="calendar"
          :enable-time="enableTime"
          :time24hr="time24h"
        />
      </BaseInputGroup>

      <BaseInputGroup
        :label="$t('invoices.due_date')"
        :content-loading="isLoading"
      >
        <BaseDatePicker
          v-model="invoiceStore.newInvoice.due_date"
          :content-loading="isLoading"
          :calendar-button="true"
          calendar-button-icon="calendar"
        />
      </BaseInputGroup>

      <div class="md:col-span-2 grid grid-cols-1 gap-6 md:grid-cols-2">
        <SingleCustomField
          v-if="isEdit && projectCustomField"
          :custom-field-scope="customFieldScope"
          :store="invoiceStore"
          store-prop="newInvoice"
          :index="projectCustomField.index"
          :field="projectCustomField.field"
        />

        <BaseInputGroup
          :label="$t('invoices.invoice_number')"
          :content-loading="isLoading"
          :error="v.invoice_number.$error && v.invoice_number.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="invoiceStore.newInvoice.invoice_number"
            :content-loading="isLoading"
            @input="v.invoice_number.$touch()"
          />
        </BaseInputGroup>
      </div>

      <ExchangeRateConverter
        :store="invoiceStore"
        store-prop="newInvoice"
        :v="v"
        :is-loading="isLoading"
        :is-edit="isEdit"
        :customer-currency="invoiceStore.newInvoice.currency_id"
      />
    </BaseInputGrid>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import ExchangeRateConverter from '@/scripts/admin/components/estimate-invoice-common/ExchangeRateConverter.vue'
import SingleCustomField from '@/scripts/admin/components/custom-fields/CreateCustomFieldsSingle.vue'
import { useInvoiceStore } from '@/scripts/admin/stores/invoice'
import { useCompanyStore } from '@/scripts/admin/stores/company'

const projectCustomFieldSlug = 'CUSTOM_INVOICE_PROYECTO'

const props = defineProps({
  v: {
    type: Object,
    default: null,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
  customFieldScope: {
    type: String,
    required: true,
  },
})

const invoiceStore = useInvoiceStore()
const companyStore = useCompanyStore()

const enableTime = computed(() => {
  return (
    companyStore.selectedCompanySettings.invoice_use_time === 'YES'
  );
})
const time24h = computed(() => {
  return (
    companyStore.selectedCompanySettings.carbon_time_format.indexOf('H') > -1
  );
})

const projectCustomField = computed(() => {
  const index = invoiceStore.newInvoice.customFields.findIndex((field) => {
    return field.slug === projectCustomFieldSlug
  })

  if (index === -1) {
    return null
  }

  return {
    field: invoiceStore.newInvoice.customFields[index],
    index,
  }
})

</script>
