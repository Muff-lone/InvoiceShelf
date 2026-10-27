import type { CustomFieldValue } from './custom-field'
import type { Address } from './user'
import type { Role } from './role'
import type { User } from './user'

export interface Company {
  id: number
  name: string
  ico?: string | null
  vat_id: string | null
  tax_id: string | null
  vat_payer?: boolean
  registry_info?: string | null
  bank_name?: string | null
  bank_account_holder?: string | null
  bank_iban?: string | null
  bank_bic?: string | null
  bank_account_prefix?: string | null
  bank_account_number?: string | null
  bank_code?: string | null
  logo: string | null
  logo_path: string | null
  unique_hash: string
  owner_id: number
  slug: string
  created_at: string
  updated_at: string
  address?: Address
  owner?: User
  user_role?: string | null
  /** Answers to the company's own custom fields, when it has any. */
  fields?: CustomFieldValue[]
}

export interface CompanySetting {
  id: number
  company_id: number
  option: string
  value: string | null
}

export interface CompanyInvitation {
  id: number
  company_id: number
  email: string
  token: string
  status: CompanyInvitationStatus
  expires_at: string
  created_at: string
  company?: Company
  role?: Role
  invited_by?: User
}

export enum CompanyInvitationStatus {
  PENDING = 'pending',
  ACCEPTED = 'accepted',
  DECLINED = 'declined',
  EXPIRED = 'expired',
}
