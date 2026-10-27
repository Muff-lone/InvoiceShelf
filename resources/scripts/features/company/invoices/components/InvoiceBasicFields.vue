<template>
  <div class="grid grid-cols-12 gap-4 mt-5 mb-6 md:gap-8 md:mb-8">
    <BaseCustomerSelectPopup
      :valid="v.customer_id"
      :content-loading="isLoading"
      type="invoice"
      class="col-span-12 lg:col-span-6 pe-0"
    />

    <RecurringFields
      v-if="isRecurring"
      :is-loading="isLoading"
      :is-edit="isEdit"
      :custom-fields="customFields"
      :custom-field-scope="customFieldScope"
    />

    <BaseInputGrid
      v-else
      class="col-span-12 p-4 border lg:col-span-6 glass rounded-xl md:p-5"
    >
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

      <BaseInputGroup
        :label="$t('invoices.taxable_supply_date')"
        :content-loading="isLoading"
      >
        <BaseDatePicker
          v-model="invoiceStore.newInvoice.taxable_supply_date"
          :content-loading="isLoading"
          :calendar-button="true"
          calendar-button-icon="calendar"
        />
      </BaseInputGroup>

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

      <BaseInputGroup
        :label="$t('invoices.payment_method')"
        :content-loading="isLoading"
      >
        <BaseMultiselect
          v-model="invoiceStore.newInvoice.payment_method"
          :options="paymentMethodOptions"
          value-prop="value"
          label="label"
          track-by="label"
          :content-loading="isLoading"
          :can-deselect="false"
          :can-clear="false"
        />
      </BaseInputGroup>

      <BaseInputGroup
        :label="$t('invoices.variable_symbol')"
        :content-loading="isLoading"
      >
        <BaseInput
          v-model="invoiceStore.newInvoice.variable_symbol"
          :content-loading="isLoading"
          type="text"
        />
      </BaseInputGroup>

      <BaseInputGroup
        :label="$t('invoices.constant_symbol')"
        :content-loading="isLoading"
      >
        <BaseInput
          v-model="invoiceStore.newInvoice.constant_symbol"
          :content-loading="isLoading"
          placeholder="0308"
          type="text"
        />
      </BaseInputGroup>

      <BaseInputGroup
        :label="$t('invoices.specific_symbol')"
        :content-loading="isLoading"
      >
        <BaseInput
          v-model="invoiceStore.newInvoice.specific_symbol"
          :content-loading="isLoading"
          type="text"
        />
      </BaseInputGroup>

      <ExchangeRateConverter
        :store="invoiceStore"
        store-prop="newInvoice"
        :v="v"
        :is-loading="isLoading"
        :is-edit="isEdit"
        :customer-currency="invoiceStore.newInvoice.currency_id"
      />

      <!-- Document-level custom fields sit with the number and the dates:
           they are attributes of the document, not a separate section. -->
      <CustomFieldInput
        v-for="field in customFields"
        :key="field.id"
        :custom-field-scope="customFieldScope"
        :field="field"
      />
    </BaseInputGrid>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { ExchangeRateConverter } from '../../../shared/document-form'
import { useInvoiceStore } from '../store'
import RecurringFields from './RecurringFields.vue'
import CustomFieldInput from '@/scripts/features/shared/custom-fields/CustomFieldInput.vue'
import { useCustomFields } from '@/scripts/features/shared/custom-fields/use-custom-fields'

interface ValidationField {
  $error: boolean
  $errors: Array<{ $message: string }>
  $touch: () => void
}

interface Props {
  v: Record<string, ValidationField>
  isLoading?: boolean
  isEdit?: boolean
  isRecurring?: boolean
  companySettings?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  isEdit: false,
  isRecurring: false,
  companySettings: () => ({}),
})

const invoiceStore = useInvoiceStore()
const { t } = useI18n()
const customFieldScope = 'newInvoice'

const paymentMethodOptions = computed(() => [
  { value: 'bank_transfer', label: t('invoices.payment_methods.bank_transfer') },
  { value: 'cash', label: t('invoices.payment_methods.cash') },
  { value: 'card', label: t('invoices.payment_methods.card') },
  { value: 'cod', label: t('invoices.payment_methods.cod') },
])

const customFields = useCustomFields({
  store: invoiceStore,
  storeProp: 'newInvoice',
  type: 'Invoice',
  isEdit: () => props.isEdit === true,
})


const enableTime = computed<boolean>(() => {
  return props.companySettings?.invoice_use_time === 'YES'
})

const time24h = computed<boolean>(() => {
  const format = props.companySettings?.carbon_time_format ?? ''
  return format.indexOf('H') > -1
})
</script>
