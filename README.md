# Invoice Management API Endpoints

## Create a Single Invoice

**Endpoint:** `POST /api/invoices`

**Request:**
```json
{
  "supplier_id": "A123456789X",
  "debtor_id": "P123456789Q",
  "debtor_name": "Global Suppliers Ltd",
  "invoice_amount": 150.75,
  "due_date": "2025-04-01",
  "invoice_date": "2025-03-01",
  "invoice_reference": "INV-ACME-001"
}
```

**Response:**
```json
{
  "success": true,
  "created": [
    {
      "index": 0,
      "invoice_id": 1,
      "invoice_reference": "INV-ACME-001"
    }
  ],
  "skipped": [],
  "errors": []
}
```

## Create Multiple Invoices

**Endpoint:** `POST /api/invoices`

**Request:**
```json
{
  "invoices": [
    {
      "supplier_id": "A123456789X",
      "debtor_id": "P123456789Q",
      "debtor_name": "Global Suppliers Ltd",
      "invoice_amount": 150.75,
      "due_date": "2025-04-01",
      "invoice_date": "2025-03-01",
      "invoice_reference": "INV-ACME-001"
    },
    {
      "supplier_id": "A123456789X",
      "debtor_id": "M987654321N",
      "debtor_name": "Metro Distributors",
      "invoice_amount": 325.50,
      "due_date": "2025-03-15",
      "invoice_date": "2025-02-15",
      "invoice_reference": "INV-ACME-002"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "created": [
    {
      "index": 0,
      "invoice_id": 1,
      "invoice_reference": "INV-ACME-001"
    },
    {
      "index": 1,
      "invoice_id": 2,
      "invoice_reference": "INV-ACME-002"
    }
  ],
  "skipped": [],
  "errors": []
}
```

## Get Invoice by Number

**Endpoint:** `GET /api/invoices/{invoice_number}`

**Example:** `GET /api/invoices/INV-ACME-001`

**Response:**
```json
{
  "success": true,
  "data": {
    "invoice": {
      "id": 1,
      "business_id": 1,
      "debtor_id": 1,
      "invoice_number": "INV-ACME-001",
      "invoice_date": "2025-03-01",
      "due_date": "2025-04-01",
      "invoice_amount": "150.75",
      "due_amount": "150.75",
      "created_at": "2025-03-04T10:15:30.000000Z",
      "updated_at": "2025-03-04T10:15:30.000000Z",
      "deleted_at": null,
      "business": {
        "id": 1,
        "name": "Acme Corporation",
        "email": "info@acme.co.ke",
        "address": "123 Main St, Nairobi",
        "registration_number": "A123456789X",
        "phone": "+254700123456",
        "email_verified_at": "2025-03-04T10:15:30.000000Z",
        "created_at": "2025-03-04T10:15:30.000000Z",
        "updated_at": "2025-03-04T10:15:30.000000Z",
        "deleted_at": null
      },
      "debtor": {
        "id": 1,
        "kra_pin": "P123456789Q",
        "name": "Global Suppliers Ltd",
        "email": "accounts@globalsuppliers.co.ke",
        "status": "active",
        "status_notes": null,
        "status_updated_by": null,
        "status_updated_at": "2025-03-04T10:15:30.000000Z",
        "listing_goes_live_at": "2025-03-09T10:15:30.000000Z",
        "listed_at": null,
        "verification_token": null,
        "created_at": "2025-03-04T10:15:30.000000Z",
        "updated_at": "2025-03-04T10:15:30.000000Z",
        "deleted_at": null
      }
    },
    "total_amount_owed": "150.75",
    "related_invoices": []
  }
}
```

## Get Invoices by Debtor KRA PIN

**Endpoint:** `GET /api/invoices/by-debtor/{kra_pin}`

**Example:** `GET /api/invoices/by-debtor/P123456789Q`

**Response:**
```json
{
  "success": true,
  "data": {
    "debtor": {
      "id": 1,
      "name": "Global Suppliers Ltd",
      "kra_pin": "P123456789Q",
      "email": "accounts@globalsuppliers.co.ke",
      "status": "active"
    },
    "invoices": [
      {
        "id": 22,
        "invoice_number": "INV-ACME-008",
        "invoice_date": "2025-05-01",
        "due_date": "2025-06-01",
        "invoice_amount": "325.75",
        "due_amount": "325.75",
        "business": {
          "id": 1,
          "name": "Acme Corporation",
          "kra_pin": "A123456789X",
          "email": "info@acme.co.ke",
          "phone": "+254700123456"
        },
        "is_overdue": false,
        "days_to_due": 89
      },
      {
        "id": 1,
        "invoice_number": "INV-ACME-001",
        "invoice_date": "2025-03-01",
        "due_date": "2025-04-01",
        "invoice_amount": "150.75",
        "due_amount": "150.75",
        "business": {
          "id": 1,
          "name": "Acme Corporation",
          "kra_pin": "A123456789X",
          "email": "info@acme.co.ke",
          "phone": "+254700123456"
        },
        "is_overdue": true,
        "days_to_due": -2
      },
      {
        "id": 12,
        "invoice_number": "INV-XYZ-006",
        "invoice_date": "2025-03-01",
        "due_date": "2025-03-30",
        "invoice_amount": "125.50",
        "due_amount": "125.50",
        "business": {
          "id": 2,
          "name": "XYZ Enterprises",
          "kra_pin": "B987654321Y",
          "email": "contact@xyz.co.ke",
          "phone": "+254711234567"
        },
        "is_overdue": true,
        "days_to_due": -5
      }
    ],
    "total_count": 3,
    "total_debt": 602.00
  }
}
```

## Get Invoices by Business KRA PIN

**Endpoint:** `GET /api/invoices/by-business/{kra_pin}`

**Example:** `GET /api/invoices/by-business/A123456789X`

**Response:**
```json
{
  "success": true,
  "data": {
    "business": {
      "id": 1,
      "name": "Acme Corporation",
      "kra_pin": "A123456789X",
      "email": "info@acme.co.ke",
      "phone": "+254700123456"
    },
    "debtor_summaries": [
      {
        "debtor": {
          "id": 1,
          "name": "Global Suppliers Ltd",
          "kra_pin": "P123456789Q",
          "email": "accounts@globalsuppliers.co.ke",
          "status": "active"
        },
        "invoices": [
          {
            "id": 22,
            "invoice_number": "INV-ACME-008",
            "invoice_date": "2025-05-01",
            "due_date": "2025-06-01",
            "invoice_amount": "325.75",
            "due_amount": "325.75",
            "debtor": {
              "id": 1,
              "name": "Global Suppliers Ltd",
              "kra_pin": "P123456789Q",
              "email": "accounts@globalsuppliers.co.ke",
              "status": "active"
            },
            "is_overdue": false,
            "days_to_due": 89
          },
          {
            "id": 1,
            "invoice_number": "INV-ACME-001",
            "invoice_date": "2025-03-01",
            "due_date": "2025-04-01",
            "invoice_amount": "150.75",
            "due_amount": "150.75",
            "debtor": {
              "id": 1,
              "name": "Global Suppliers Ltd",
              "kra_pin": "P123456789Q",
              "email": "accounts@globalsuppliers.co.ke",
              "status": "active"
            },
            "is_overdue": true,
            "days_to_due": -2
          }
        ],
        "invoice_count": 2,
        "total_owed": 476.50
      },
      {
        "debtor": {
          "id": 2,
          "name": "Metro Distributors",
          "kra_pin": "M987654321N",
          "email": "finance@metrodist.co.ke",
          "status": "active"
        },
        "invoices": [
          {
            "id": 23,
            "invoice_number": "INV-ACME-009",
            "invoice_date": "2025-04-15",
            "due_date": "2025-05-15",
            "invoice_amount": "475.50",
            "due_amount": "475.50",
            "debtor": {
              "id": 2,
              "name": "Metro Distributors",
              "kra_pin": "M987654321N",
              "email": "finance@metrodist.co.ke",
              "status": "active"
            },
            "is_overdue": false,
            "days_to_due": 72
          },
          {
            "id": 2,
            "invoice_number": "INV-ACME-002",
            "invoice_date": "2025-02-15",
            "due_date": "2025-03-15",
            "invoice_amount": "325.50",
            "due_amount": "325.50",
            "debtor": {
              "id": 2,
              "name": "Metro Distributors",
              "kra_pin": "M987654321N",
              "email": "finance@metrodist.co.ke",
              "status": "active"
            },
            "is_overdue": true,
            "days_to_due": -20
          }
        ],
        "invoice_count": 2,
        "total_owed": 801.00
      }
    ],
    "total_invoice_count": 10,
    "total_amount_owed": 4752.55
  }
}
```

## Get Debtor by KRA PIN

**Endpoint:** `GET /api/debtors/{kra_pin}`

**Example:** `GET /api/debtors/P123456789Q`

**Response:**
```json
{
  "success": true,
  "data": {
    "debtor": {
      "id": 1,
      "name": "Global Suppliers Ltd",
      "kra_pin": "P123456789Q",
      "email": "accounts@globalsuppliers.co.ke",
      "status": "active",
      "is_also_business": false
    },
    "business_relationships": [
      {
        "business_id": 1,
        "business_name": "Acme Corporation",
        "business_kra_pin": "A123456789X",
        "amount_owed": "476.50"
      },
      {
        "business_id": 2,
        "business_name": "XYZ Enterprises",
        "business_kra_pin": "B987654321Y",
        "amount_owed": "125.50"
      }
    ],
    "total_debt": 602.00
  },
  "business_record": null
}
```

## List All Debtors

**Endpoint:** `GET /api/debtors`

**Example:** `GET /api/debtors?per_page=2`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Global Suppliers Ltd",
      "kra_pin": "P123456789Q",
      "email": "accounts@globalsuppliers.co.ke",
      "status": "active",
      "business_relationships": [
        {
          "business_id": 1,
          "business_name": "Acme Corporation",
          "business_kra_pin": "A123456789X",
          "amount_owed": "476.50"
        },
        {
          "business_id": 2,
          "business_name": "XYZ Enterprises",
          "business_kra_pin": "B987654321Y",
          "amount_owed": "125.50"
        }
      ],
      "total_debt": 602.00,
      "is_also_business": false
    },
    {
      "id": 2,
      "name": "Metro Distributors",
      "kra_pin": "M987654321N",
      "email": "finance@metrodist.co.ke",
      "status": "active",
      "business_relationships": [
        {
          "business_id": 1,
          "business_name": "Acme Corporation",
          "business_kra_pin": "A123456789X",
          "amount_owed": "801.00"
        }
      ],
      "total_debt": 801.00,
      "is_also_business": false
    }
  ],
  "pagination": {
    "total": 33,
    "per_page": 2,
    "current_page": 1,
    "last_page": 17
  }
}
```

## Search Debtors

**Endpoint:** `GET /api/debtors/search?query={search_term}`

**Example:** `GET /api/debtors/search?query=Global`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Global Suppliers Ltd",
      "kra_pin": "P123456789Q",
      "email": "accounts@globalsuppliers.co.ke",
      "status": "active",
      "business_relationships": [
        {
          "business_id": 1,
          "business_name": "Acme Corporation",
          "business_kra_pin": "A123456789X",
          "amount_owed": "476.50"
        },
        {
          "business_id": 2,
          "business_name": "XYZ Enterprises",
          "business_kra_pin": "B987654321Y",
          "amount_owed": "125.50"
        }
      ],
      "total_debt": 602.00,
      "is_also_business": false
    }
  ]
}
```
