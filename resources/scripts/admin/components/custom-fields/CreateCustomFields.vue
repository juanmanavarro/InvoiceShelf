<template>
  <div
    v-if="
      store[storeProp] && visibleCustomFields.length > 0 && !isLoading
    "
  >
    <BaseInputGrid :layout="gridLayout">
      <SingleField
        v-for="entry in visibleCustomFields"
        :key="entry.field.id"
        :custom-field-scope="customFieldScope"
        :store="store"
        :store-prop="storeProp"
        :index="entry.index"
        :field="entry.field"
      />
    </BaseInputGrid>
  </div>
</template>

<script setup>
import moment from 'moment'
import lodash from 'lodash'
import { useCustomFieldStore } from '@/scripts/admin/stores/custom-field'
import { computed, watch } from 'vue'
import SingleField from './CreateCustomFieldsSingle.vue'

const customFieldStore = useCustomFieldStore()

const props = defineProps({
  store: {
    type: Object,
    required: true,
  },
  storeProp: {
    type: String,
    required: true,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: null,
  },
  gridLayout: {
    type: String,
    default: 'two-column',
  },
  isLoading: {
    type: Boolean,
    default: null,
  },
  customFieldScope: {
    type: String,
    required: true,
  },
  excludeSlugs: {
    type: Array,
    default: () => [],
  },
})

const visibleCustomFields = computed(() => {
  return props.store[props.storeProp].customFields
    .map((field, index) => ({ field, index }))
    .filter((entry) => {
      const slug = entry.field.slug || entry.field.custom_field?.slug

      return !props.excludeSlugs.includes(slug)
    })
})

getInitialCustomFields()

function mergeExistingValues() {
  if (props.isEdit) {
    props.store[props.storeProp].fields.forEach((field) => {
      const existingIndex = props.store[props.storeProp].customFields.findIndex(
        (f) => f.id === field.custom_field_id
      )

      if (existingIndex > -1) {
        let value = field.default_answer

        if (value && field.custom_field.type === 'DateTime') {
          value = moment(field.default_answer, 'YYYY-MM-DD HH:mm:ss').format(
            'YYYY-MM-DD HH:mm'
          )
        }

        props.store[props.storeProp].customFields[existingIndex] = {
          ...field,
          id: field.custom_field_id,
          value: value,
          slug: field.custom_field.slug,
          type: field.custom_field.type,
          label: field.custom_field.label,
          options: field.custom_field.options,
          is_required: field.custom_field.is_required,
          placeholder: field.custom_field.placeholder,
          order: field.custom_field.order,
        }
      }
    })
  }
}

async function getInitialCustomFields() {
  const res = await customFieldStore.fetchCustomFields({
    type: props.type,
    limit: 'all',
  })

  let data = res.data.data

  data.map((d) => (d.value = d.default_answer))

  props.store[props.storeProp].customFields = lodash.sortBy(
    data,
    (_cf) => _cf.order
  )

  mergeExistingValues()
}

watch(
  () => props.store[props.storeProp].fields,
  (val) => {
    mergeExistingValues()
  }
)
</script>
