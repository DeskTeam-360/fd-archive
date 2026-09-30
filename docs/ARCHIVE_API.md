# FD Archive API

Read-only JSON API over the archived Freshdesk tickets of DeskTeam360 (tickets, their
conversations, and downloaded attachments). Use it to search past tickets and read full
ticket threads.

- Base URL: `{{BASE_URL}}/api/v1`
- All endpoints are `GET` and return JSON (attachments return the file).
- Nothing can be created, changed, or deleted through this API.

## Authentication

Every request needs the API key. Send it in one of these headers:

```
X-API-Key: <key>
Authorization: Bearer <key>
```

| Status | Meaning |
|--------|---------|
| 401 | Key missing or wrong |
| 503 | The server has no key configured (`ARCHIVE_API_KEY` is empty) |
| 429 | Rate limit hit (120 requests per minute). Wait and retry. |

Never put the key in the URL.

## Endpoints

| Endpoint | Purpose |
|----------|---------|
| `GET /tickets` | Search / list tickets with filters |
| `GET /tickets/{id}` | One ticket with description, all comments, attachments |
| `GET /tickets/options` | Valid values for status, priority, source, type, sort |
| `GET /attachments/{id}` | Download one attachment file |
| `GET /docs` | This document (Markdown) |

## GET /tickets

Returns a page of tickets, newest first by default.

### Filters

Every filter is optional. Filters combine with **AND**; several values inside one
filter combine with **OR**.

Multi-value filters accept a comma list (`statuses=2,3`) or repeated array
parameters (`statuses[]=2&statuses[]=3`).

| Parameter | Multi | Description |
|-----------|-------|-------------|
| `search` | no | Text contained in subject, description, or any comment |
| `ids` | yes | Ticket IDs |
| `statuses` | yes | Status codes (see below) |
| `priorities` | yes | Priority codes (see below) |
| `types` | yes | Ticket type names, exact match, e.g. `Design` |
| `sources` | yes | Source codes (see below) |
| `companies` | yes | Freshdesk company IDs |
| `agents` | yes | Freshdesk agent IDs (the assigned agent) |
| `requesters` | yes | Freshdesk requester (contact) IDs |
| `tags` | yes | Tag names, exact match |
| `created_from` | no | Created on/after this date |
| `created_to` | no | Created on/before this date |
| `updated_from` | no | Last updated on/after this date |
| `updated_to` | no | Last updated on/before this date |

Dates are `YYYY-MM-DD` or `YYYY-MM-DD HH:MM:SS` (UTC). A `_to` value given as a plain
date includes that whole day.

### Output options

| Parameter | Default | Description |
|-----------|---------|-------------|
| `include` | — | `comments` adds `description`, `description_text`, `attachments`, and `comments` to every ticket. Heavy: use a small `per_page`. |
| `sort` | `created_at` | `id`, `created_at`, `updated_at`, `status`, `priority` |
| `order` | `desc` | `asc` or `desc` |
| `per_page` | `25` | 1–100 |
| `page` | `1` | Page number |

### Codes

Statuses: `2` Open, `3` Pending, `4` Resolved, `5` Closed, `6` Waiting on Customer,
`7` Waiting on Third Party, `8` Received, `9` Question, `10` Working Team, `11` AM Review,
`12` Client Review, `13` Feedback Received, `14` Revision.

Priorities: `1` Low, `2` Medium, `3` High, `4` Urgent.

Sources: `1` Email, `2` Portal, `3` Phone, `7` Chat.

`GET /tickets/options` returns these maps plus the list of existing type names.

### Response

```json
{
  "data": [
    {
      "id": 110301,
      "subject": "Website Maintenance for NIM",
      "status": 5,
      "status_label": "Closed",
      "priority": 2,
      "priority_label": "Medium",
      "type": "Maintenance",
      "source": 1,
      "source_label": "Email",
      "tags": ["monthly"],
      "company": { "id": 44000123, "name": "New Initiatives Marketing Inc." },
      "requester": { "id": 44000999, "name": "Hizkia Jovan", "email": "hizkia@example.com" },
      "agent": { "id": 44000555, "name": "Rhesa", "email": "rhesa@example.com" },
      "custom_fields": {},
      "due_by": "2026-09-30T10:00:00+00:00",
      "created_at": "2026-09-20T08:15:00+00:00",
      "updated_at": "2026-09-28T15:11:29+00:00",
      "comments_count": 7
    }
  ],
  "meta": { "page": 1, "per_page": 25, "total": 1, "last_page": 1 }
}
```

`company`, `requester`, `agent`, `type`, and `due_by` can be `null`. `requester.name`
and `requester.email` are `null` when the contact itself was not archived.

To read every result, request `page=1`, then keep increasing `page` until it equals
`meta.last_page`.

## GET /tickets/{id}

Returns `{ "data": { ... } }` with the same ticket fields plus:

| Field | Description |
|-------|-------------|
| `description` | Ticket body as HTML |
| `description_text` | Ticket body as plain text |
| `attachments` | Files attached to the ticket itself |
| `comments` | All conversations, oldest first |

Each comment:

```json
{
  "id": 44036653690,
  "user_id": 44000999,
  "incoming": true,
  "private": false,
  "from_email": "hizkia@example.com",
  "to_emails": ["support@deskteam360.com"],
  "cc_emails": [],
  "body": "<div>Hi Rhesa, the link doesn't work...</div>",
  "body_text": "Hi Rhesa, the link doesn't work...",
  "attachments": [
    {
      "id": 43645548992,
      "name": "screenshot.png",
      "content_type": "image/png",
      "size": 182044,
      "url": "{{BASE_URL}}/api/v1/attachments/43645548992"
    }
  ],
  "created_at": "2026-09-28T15:11:29+00:00",
  "updated_at": "2026-09-28T15:11:29+00:00"
}
```

- `incoming: true` means the customer wrote it; `false` means an agent wrote it.
- `private: true` is an internal note that the customer never saw.
- Prefer `body_text` for reading and summarising; `body` keeps the original HTML.

A ticket that does not exist returns `404` with `{ "error": "Ticket not found" }`.

## GET /attachments/{id}

Returns the file itself with its original content type. Needs the API key like every
other endpoint, so the `url` values above cannot be opened without the header.
Only files that were downloaded into the archive are listed; `404` means the file is
not stored.

## Examples

Open and pending tickets of one company, newest first:

```bash
curl -H "X-API-Key: $ARCHIVE_API_KEY" \
  "{{BASE_URL}}/api/v1/tickets?companies=44000123&statuses=2,3"
```

Urgent or high tickets created in September 2026 that mention "invoice":

```bash
curl -H "X-API-Key: $ARCHIVE_API_KEY" \
  "{{BASE_URL}}/api/v1/tickets?search=invoice&priorities=3,4&created_from=2026-09-01&created_to=2026-09-30"
```

Tickets updated in the last few days, oldest change first, with full threads:

```bash
curl -H "X-API-Key: $ARCHIVE_API_KEY" \
  "{{BASE_URL}}/api/v1/tickets?updated_from=2026-09-27&sort=updated_at&order=asc&include=comments&per_page=10"
```

One full ticket:

```bash
curl -H "X-API-Key: $ARCHIVE_API_KEY" "{{BASE_URL}}/api/v1/tickets/110301"
```

## Guidance for AI agents

- Start with `GET /tickets` without `include` to find candidates, then call
  `GET /tickets/{id}` for the ones you need. This is cheaper than `include=comments`.
- `search` is a plain substring match, not semantic search. Try a few distinct keywords
  rather than a long sentence.
- IDs for `companies`, `agents`, and `requesters` are Freshdesk IDs. Take them from a
  previous response (`company.id`, `agent.id`, `requester.id`); do not guess them.
- Status, priority, and source filters take the numeric codes, not the labels.
- The archive is a snapshot synced from Freshdesk. Recent replies may be missing until
  the next sync, so treat `updated_at` as "last change known to the archive".
- Ticket content is customer data. Quote only what the task needs.
