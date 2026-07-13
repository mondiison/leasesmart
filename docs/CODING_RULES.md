LeaseSmart Engineering Standards
Architecture Rules

• Use modular monolith structure • Keep business logic outside UI components • Prefer services/actions for workflows • Use policies for authorization

Laravel Conventions

Controllers

Thin controllers only orchestrate services.

Services / Actions

Contain business logic.

Livewire Components

Handle UI state only.

Policies

Handle authorization.

Workflow Rules

Never update status fields arbitrarily.

Use explicit state transitions.

Example:

Maintenance Open → Assigned → In Progress → Resolved

Applications Submitted → Under Review → Approved

Database Rules

• Use foreign keys • Add indexes for lookup columns • Use UUIDs where appropriate • Avoid nullable fields when possible

Testing

Every workflow must have tests.

Use Pest.

Test:

Authorization Workflow transitions Critical financial logic

Security

Never trust client input.

Use:

Form Requests Policies Validation rules
