// --- Typy pomocnicze ---
type TSkillLevel = 1 | 2 | 3 | 4 | 5;
type TLanguage = 'en' | 'pl' | 'de' | 'fr' | string;
type TCurrency = 'USD' | 'EUR' | 'PLN' | string;
type TTimezone = 'UTC' | 'CET' | 'EST' | string;
type TDateTime = string; // Date string, e.g. "2025-04-12T14:30:00Z"

// --- Typy statusów ---
type TProjectStatus = 'active' | 'archived' | 'completed';
type TTaskStatus = 'todo' | 'in_progress' | 'done' | 'archived';
type TInvoiceStatus = 'draft' | 'sent' | 'paid' | 'overdue';
type TTaskPriority = 'low' | 'medium' | 'high';

// --- Typy aplikacji ---
export interface IAppBranding {
  appName?: string;
  logoUrl?: string;
  primaryColor?: string;
  footerText?: string;
}

export interface IAppIntegrations {
  googleLoginEnabled?: boolean;
  gitlabLoginEnabled?: boolean;
  githubLoginEnabled?: boolean;
  stripeEnabled?: boolean;
}

export interface ITenantPreferences {
  invoiceAutoNumbering?: boolean;
  defaultInvoiceDueDays?: number;
  productSorting?: 'name' | 'price' | 'createdAt';
  [key: string]: any;
}

export interface IAppSettings {
  language: TLanguage;
  currency: TCurrency;
  timezone: TTimezone;
  theme: 'light' | 'dark';
  registrationEnabled: boolean;
  requireEmailVerification: boolean;
  defaultVatRateId?: string;
  defaultUnitId?: string;
  defaultInvoiceTypeId?: string;
  branding?: IAppBranding;
  integrations?: IAppIntegrations;
  createdAt: TDateTime;
  updatedAt: TDateTime;
}

export interface ITenantDefaults {
  tenantId: string;
  language?: TLanguage;
  currency?: TCurrency;
  timezone?: TTimezone;
  theme?: 'light' | 'dark';
  defaultVatRateId?: string;
  defaultUnitId?: string;
  defaultInvoiceTypeId?: string;
  preferences?: ITenantPreferences;
  updatedAt: TDateTime;
}

// --- Autoryzacja i Multi-Tenancy ---
export interface IUser {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  password: string;
  description?: string;
  birthDate?: string;
  isAdmin: boolean;
  createdAt: TDateTime;
  updatedAt: TDateTime;
  // emailConfirmedAt?
}

export interface IUserSettings {
  userId: string;
  language: TLanguage;
  theme: 'light' | 'dark' | 'system';
  timezone?: TTimezone;
  twoFactorEnabled: boolean;
  twoFactorConfirmed: boolean;
  preferences?: Record<string, any>;
}

export interface IUserOAuthAccount {
  id: string;
  userId: string;
  provider: 'google' | 'gitlab' | 'github' | 'microsoft' | string;
  providerUserId: string;
  email?: string;
  linkedAt: TDateTime;
}

export interface ITenant {
  id: string;
  name: string;
  slug: string;
  createdAt: TDateTime;
  updatedAt: TDateTime;
}

export interface ITenantSettings {
  tenantId: string;
  language: TLanguage;
  currency: TCurrency;
  timezone?: TTimezone;
  require2FA: boolean;
  invoicePrefix?: string;
  branding?: {
    logoUrl?: string;
    primaryColor?: string;
    subdomain?: string;
  };
  preferences?: Record<string, any>;
}

export interface IUserTenant {
  id: string;
  userId: string;
  tenantId: string;
  role: string;
  permissions?: string[];
  joinedAt: TDateTime;
}

// --- Role i Uprawnienia ---
export interface IRole {
  id: string;
  name: string;
  description?: string;
  permissions: string[];
}

export interface IPermission {
  id: string;
  name: string;
  description?: string;
}

// --- Projekty i Zadania ---
export interface IProject {
  id: string;
  tenantId: string;
  name: string;
  description?: string;
  status: TProjectStatus;
  ownerId: string;
  createdAt: TDateTime;
  updatedAt: TDateTime;
  /**
   * Comments and attachments can be added to a project.
   */
}

export interface IProjectRole {
  id: string;
  tenantId: string;
  name: string;
  description?: string;
  permissions?: string[]; // Permissions are typically the names of the actions (e.g., "view", "edit", "delete")
  createdAt: TDateTime;
  updatedAt: TDateTime;
}

export interface IProjectUser {
  projectId: string;
  userId: string;
  projectRoleId: string;
}

export interface ITask {
  id: string;
  projectId: string;
  title: string;
  description?: string;
  status: TTaskStatus;
  priority: TTaskPriority;
  assignedToId?: string;
  createdById: string;
  dueDate?: TDateTime;
  createdAt: TDateTime;
  updatedAt: TDateTime;
  /**
   * Comments and attachments can be added to a task.
   */
}

export interface ITaskWatcher {
  taskId: string;
  userId: string;
}

// --- Komentarze i Tagi (Morphable) ---
export interface IComment {
  id: string;
  userId: string;
  content: string;
  commentableId: string;
  commentableType: 'Task' | 'Project' | 'Contractor' | string;
  createdAt: TDateTime;
}

export interface ITag {
  id: string;
  name: string;
  color?: string;
}

export interface ITaggable {
  tagId: string;
  taggableId: string;
  taggableType: string;
}

// --- Kontrahenci ---
export interface IContractor {
  id: string;
  tenantId: string;
  name: string;
  taxId?: string;
  email?: string;
  phone?: string;
  description?: string;
  isSupplier: boolean;
  isBuyer: boolean;
  createdAt: TDateTime;
  updatedAt: TDateTime;
}

export interface IContractorAddress {
  id: string;
  contractorId: string;
  label: string;
  countryId: string;
  street: string;
  city: string;
  zip: string;
  isDefault: boolean;
}

export interface IContractorBankAccount {
  id: string;
  contractorId: string;
  bankName: string;
  iban: string;
  currencies: TCurrency[];
  isDefault: boolean;
}

export interface IContractorContactPerson {
  id: string;
  contractorId: string;
  name: string;
  email?: string;
  phone?: string;
  position?: string;
}

// --- Faktury i Produkty ---
export interface IInvoiceType {
  id: string;
  name: string;
  isCorrection: boolean;
  isVat: boolean;
  isAccounting: boolean;
}

export interface IInvoice {
  id: string;
  tenantId: string;
  contractorId: string;
  invoiceTypeId: string;
  invoiceNumber: string;
  issueDate: TDateTime;
  dueDate: TDateTime;
  totalNet: number;
  totalTax: number;
  totalGross: number;
  status: TInvoiceStatus;
  createdAt: TDateTime;
  updatedAt: TDateTime;
  /**
   * Comments and attachments can be added to an invoice.
   */
}

export interface IInvoiceItem {
  id: string;
  invoiceId: string;
  productId?: string;
  name: string;
  quantity: number;
  unitId: string;
  priceNet: number;
  vatRateId: string;
  totalNet: number;
  totalTax: number;
  totalGross: number;
}

export interface IProduct {
  id: string;
  tenantId: string;
  name: string;
  description?: string;
  unitId: string;
  priceNet: number;
  vatRateId: string;
  createdAt: TDateTime;
  updatedAt: TDateTime;
}

// --- Słowniki ---
export interface ICountry {
  id: string;
  code: string;
  name: string;
}

export interface IUnit {
  id: string;
  code: string;
  name: string;
}

export interface IVatRate {
  id: string;
  name: string;
  rate: number;
}

// --- Umiejętności (Skills) ---
export interface ISkillCategory {
  id: string;
  name: string;
  description?: string;
}

export interface ISkill {
  id: string;
  categoryId: string;
  name: string;
  description?: string;
}

export interface IUserSkill {
  userId: string;
  skillId: string;
  level: TSkillLevel;
  acquiredAt?: TDateTime;
}

export interface IProjectRequiredSkill {
  projectId: string;
  skillId: string;
  requiredLevel: TSkillLevel;
}

// --- Załączniki (Attachments) ---
export interface IAttachment {
  id: string;
  fileName: string;
  fileUrl: string;
  fileType: string;
  fileSize: number;
  createdAt: TDateTime;
  updatedAt: TDateTime;
  /**
   * Attachments can be added to projects, tasks, contractors, and invoices.
   */
}

export interface IAttachmentable {
  attachmentId: string;
  attachmentableId: string;
  attachmentableType: 'Task' | 'Project' | 'Contractor' | 'Invoice' | string;
}

