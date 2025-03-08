# Invoice Management API Endpoints

## Invoice Endpoints

### List All Invoices

**Endpoint:** `GET /api/invoices`

**Query Parameters:**
- `per_page` (optional): Number of records per page
- `business_id` (optional): Filter by business ID
- `debtor_id` (optional): Filter by debtor ID
- `is_overdue` (optional): Filter by overdue status (true/false)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "business_id": 1,
      "debtor_id": 1,
      "invoice_number": "INV-ACME-001",
      "invoice_date": "2025-03-01",
      "due_date": "2025-04-01",
      "invoice_amount": "150.75",
      "due_amount": "150.75",
      "payment_terms": 31,
      "days_overdue": 2,
      "dbt_ratio": 0.0645,
      "created_at": "2025-03-04T10:15:30.000000Z",
      "updated_at": "2025-03-04T10:15:30.000000Z",
      "deleted_at": null,
      "business": {
        "id": 1,
        "name": "Acme Corporation",
        "registration_number": "A123456789X"
      },
      "debtor": {
        "id": 1,
        "name": "Global Suppliers Ltd",
        "kra_pin": "P123456789Q",
        "status": "active"
      }
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7
  }
}
```

### Create a Single Invoice

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

### Create Multiple Invoices

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

### Get Invoice by Number

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
      "payment_terms": 31,
      "days_overdue": 2,
      "dbt_ratio": 0.0645,
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
    "business_debtor_metrics": {
      "average_payment_terms": 31.0,
      "median_payment_terms": 31.0,
      "average_days_overdue": 2.0,
      "median_days_overdue": 2.0,
      "average_dbt_ratio": 0.0645,
      "median_dbt_ratio": 0.0645
    },
    "related_invoices": []
  }
}
```

### Get Invoices by Debtor KRA PIN

**Endpoint:** `GET /api/invoices/debtor/{kra_pin}` or `GET /api/invoices/by-debtor/{kra_pin}`

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
        "payment_terms": 31,
        "days_overdue": 0,
        "dbt_ratio": 0.0,
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
        "payment_terms": 31,
        "days_overdue": 2,
        "dbt_ratio": 0.0645,
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
        "payment_terms": 29,
        "days_overdue": 5,
        "dbt_ratio": 0.1724,
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
    "business_relationships": [
      {
        "business_id": 1,
        "business_name": "Acme Corporation",
        "business_kra_pin": "A123456789X",
        "amount_owed": "476.50",
        "average_payment_terms": 31.0,
        "median_payment_terms": 31.0,
        "average_days_overdue": 1.0,
        "median_days_overdue": 1.0,
        "average_dbt_ratio": 0.0323,
        "median_dbt_ratio": 0.0323
      },
      {
        "business_id": 2,
        "business_name": "XYZ Enterprises",
        "business_kra_pin": "B987654321Y",
        "amount_owed": "125.50",
        "average_payment_terms": 29.0,
        "median_payment_terms": 29.0,
        "average_days_overdue": 5.0,
        "median_days_overdue": 5.0,
        "average_dbt_ratio": 0.1724,
        "median_dbt_ratio": 0.1724
      }
    ],
    "total_count": 3,
    "total_debt": 602.00
  }
}
```

### Get Invoices by Business KRA PIN

**Endpoint:** `GET /api/invoices/business/{kra_pin}` or `GET /api/invoices/by-business/{kra_pin}`

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
        "metrics": {
          "average_payment_terms": 31.0,
          "median_payment_terms": 31.0,
          "average_days_overdue": 1.0,
          "median_days_overdue": 1.0,
          "average_dbt_ratio": 0.0323,
          "median_dbt_ratio": 0.0323
        },
        "invoices": [
          {
            "id": 22,
            "invoice_number": "INV-ACME-008",
            "invoice_date": "2025-05-01",
            "due_date": "2025-06-01",
            "invoice_amount": "325.75",
            "due_amount": "325.75",
            "payment_terms": 31,
            "days_overdue": 0,
            "dbt_ratio": 0.0,
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
            "payment_terms": 31,
            "days_overdue": 2,
            "dbt_ratio": 0.0645,
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
        "metrics": {
          "average_payment_terms": 28.5,
          "median_payment_terms": 28.5,
          "average_days_overdue": 10.0,
          "median_days_overdue": 10.0,
          "average_dbt_ratio": 0.3509,
          "median_dbt_ratio": 0.3509
        },
        "invoices": [
          {
            "id": 23,
            "invoice_number": "INV-ACME-009",
            "invoice_date": "2025-04-15",
            "due_date": "2025-05-15",
            "invoice_amount": "475.50",
            "due_amount": "475.50",
            "payment_terms": 30,
            "days_overdue": 0,
            "dbt_ratio": 0.0,
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
            "payment_terms": 27,
            "days_overdue": 20,
            "dbt_ratio": 0.7407,
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

## Debtor Endpoints

### List All Debtors

**Endpoint:** `GET /api/debtors`

**Query Parameters:**
- `per_page` (optional): Number of records per page
- `status` (optional): Filter by debtor status (active, pending, paid, disputed)
- `business_id` (optional): Filter by business ID

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
          "amount_owed": "476.50",
          "metrics": {
            "average_payment_terms": 31.0,
            "median_payment_terms": 31.0,
            "average_days_overdue": 1.0,
            "median_days_overdue": 1.0,
            "average_dbt_ratio": 0.0323,
            "median_dbt_ratio": 0.0323
          }
        },
        {
          "business_id": 2,
          "business_name": "XYZ Enterprises",
          "business_kra_pin": "B987654321Y",
          "amount_owed": "125.50",
          "metrics": {
            "average_payment_terms": 29.0,
            "median_payment_terms": 29.0,
            "average_days_overdue": 5.0,
            "median_days_overdue": 5.0,
            "average_dbt_ratio": 0.1724,
            "median_dbt_ratio": 0.1724
          }
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
          "amount_owed": "801.00",
          "metrics": {
            "average_payment_terms": 28.5,
            "median_payment_terms": 28.5,
            "average_days_overdue": 10.0,
            "median_days_overdue": 10.0,
            "average_dbt_ratio": 0.3509,
            "median_dbt_ratio": 0.3509
          }
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

### Get Debtor by KRA PIN

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
        "amount_owed": "476.50",
        "metrics": {
          "average_payment_terms": 31.0,
          "median_payment_terms": 31.0,
          "average_days_overdue": 1.0,
          "median_days_overdue": 1.0,
          "average_dbt_ratio": 0.0323,
          "median_dbt_ratio": 0.0323
        }
      },
      {
        "business_id": 2,
        "business_name": "XYZ Enterprises",
        "business_kra_pin": "B987654321Y",
        "amount_owed": "125.50",
        "metrics": {
          "average_payment_terms": 29.0,
          "median_payment_terms": 29.0,
          "average_days_overdue": 5.0,
          "median_days_overdue": 5.0,
          "average_dbt_ratio": 0.1724,
          "median_dbt_ratio": 0.1724
        }
      }
    ],
    "total_debt": 602.00
  },
  "business_record": null
}
```

### Search Debtors

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
          "amount_owed": "476.50",
          "metrics": {
            "average_payment_terms": 31.0,
            "median_payment_terms": 31.0,
            "average_days_overdue": 1.0,
            "median_days_overdue": 1.0,
            "average_dbt_ratio": 0.0323,
            "median_dbt_ratio": 0.0323
          }
        },
        {
          "business_id": 2,
          "business_name": "XYZ Enterprises",
          "business_kra_pin": "B987654321Y",
          "amount_owed": "125.50",
          "metrics": {
            "average_payment_terms": 29.0,
            "median_payment_terms": 29.0,
            "average_days_overdue": 5.0,
            "median_days_overdue": 5.0,
            "average_dbt_ratio": 0.1724,
            "median_dbt_ratio": 0.1724
          }
        }
      ],
      "total_debt": 602.00,
      "is_also_business": false
    }
  ]
}
```

## Business Endpoints

### List All Businesses

**Endpoint:** `GET /api/businesses`

**Query Parameters:**
- `per_page` (optional): Number of records per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Acme Corporation",
      "email": "info@acme.co.ke",
      "phone": "+254700123456",
      "address": "123 Main St, Nairobi",
      "registration_number": "A123456789X",
      "email_verified_at": "2025-03-04T10:15:30.000000Z",
      "created_at": "2025-03-04T10:15:30.000000Z",
      "updated_at": "2025-03-04T10:15:30.000000Z",
      "deleted_at": null,
      "users": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "ianyakundi015@gmail.com"
        }
      ]
    },
    {
      "id": 2,
      "name": "XYZ Enterprises",
      "email": "contact@xyz.co.ke",
      "phone": "+254711234567",
      "address": "456 Market Ave, Mombasa",
      "registration_number": "B987654321Y",
      "email_verified_at": "2025-03-04T10:15:30.000000Z",
      "created_at": "2025-03-04T10:15:30.000000Z",
      "updated_at": "2025-03-04T10:15:30.000000Z",
      "deleted_at": null,
      "users": [
        {
          "id": 2,
          "name": "Jane Smith",
          "email": "okonuian@gmail.com"
        }
      ]
    }
  ],
  "pagination": {
    "total": 3,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

### Get Business by KRA PIN

**Endpoint:** `GET /api/businesses/{kra_pin}`

**Example:** `GET /api/businesses/A123456789X`

**Response:**
```json
{
  "success": true,
  "data": {
    "business": {
      "id": 1,
      "name": "Acme Corporation",
      "email": "info@acme.co.ke",
      "phone": "+254700123456",
      "address": "123 Main St, Nairobi",
      "registration_number": "A123456789X",
      "email_verified_at": "2025-03-04T10:15:30.000000Z",
      "created_at": "2025-03-04T10:15:30.000000Z",
      "updated_at": "2025-03-04T10:15:30.000000Z",
      "deleted_at": null,
      "users": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "ianyakundi015@gmail.com"
        }
      ]
    },
    "debtors": [
      {
        "id": 1,
        "name": "Global Suppliers Ltd",
        "kra_pin": "P123456789Q",
        "email": "accounts@globalsuppliers.co.ke",
        "status": "active",
        "amount_owed": "476.50",
        "average_payment_terms": 31.0,
        "median_payment_terms": 31.0,
        "average_days_overdue": 1.0,
        "median_days_overdue": 1.0,
        "average_dbt_ratio": 0.0323,
        "median_dbt_ratio": 0.0323
      },
      {
        "id": 2,
        "name": "Metro Distributors",
        "kra_pin": "M987654321N",
        "email": "finance@metrodist.co.ke",
        "status": "active",
        "amount_owed": "801.00",
        "average_payment_terms": 28.5,
        "median_payment_terms": 28.5,
        "average_days_overdue": 10.0,
        "median_days_overdue": 10.0,
        "average_dbt_ratio": 0.3509,
        "median_dbt_ratio": 0.3509
      }
    ],
    "summary": {
      "total_debtors": 10,
      "total_amount_owed": 3528.45,
      "invoice_summary": {
        "total_count": 25,
        "total_amount": 4752.55,
        "total_due": 4752.55,
        "overdue_count": 7,
        "avg_payment_terms": 29.8,
        "avg_days_overdue": 5.1,
        "avg_dbt_ratio": 0.1711
      },
      "is_also_debtor": true
    }
  }
}
```

### Search Businesses

**Endpoint:** `GET /api/businesses/search?query={search_term}`

**Example:** `GET /api/businesses/search?query=Acme`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Acme Corporation",
      "email": "info@acme.co.ke",
      "phone": "+254700123456",
      "address": "123 Main St, Nairobi",
      "registration_number": "A123456789X",
      "email_verified_at": "2025-03-04T10:15:30.000000Z",
      "created_at": "2025-03-04T10:15:30.000000Z",
      "updated_at": "2025-03-04T10:15:30.000000Z",
      "deleted_at": null,
      "users": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "ianyakundi015@gmail.com"
        }
      ]
    }
  ]
}
```
