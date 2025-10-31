# Custom Design Projects Guide

Complete workflow for bespoke jewelry commissions.

## Overview

Custom Design module allows customers to submit and track custom jewelry projects. Managed via Custom Post Type with status workflow, email notifications, and customer dashboard.

## Status Workflow

```
brief_received ? concept_ready ? cad_approved ? in_production ? ready_for_pickup
      ?              ?               ?                ?                ?
   Email 1      Email 2         Email 3         (Silent)        Email 4
```

### Status Descriptions

1. **brief_received**: Initial submission, brief reviewed by team
2. **concept_ready**: Design concepts prepared for customer review
3. **cad_approved**: Customer approved CAD design, production ready
4. **in_production**: Jewelry being crafted
5. **ready_for_pickup**: Completed, awaiting customer pickup/delivery

## Project Submission

### Frontend Form

Create page with shortcode or custom form:

```html
<form id="custom-design-form" class="custom-design-form">
    <input type="text" name="name" placeholder="Imi? i nazwisko" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="tel" name="phone" placeholder="Telefon">
    
    <select name="project_type" required>
        <option value="">Rodzaj bi?uterii</option>
        <option value="ring">Pier?cionek</option>
        <option value="necklace">Naszyjnik</option>
        <option value="bracelet">Bransoletka</option>
        <option value="earrings">Kolczyki</option>
        <option value="other">Inne</option>
    </select>
    
    <textarea name="brief" placeholder="Opisz swoj? wizj?..." required></textarea>
    <input type="number" name="budget" placeholder="Bud?et (PLN)">
    <input type="date" name="deadline" placeholder="Preferowany termin">
    
    <div class="checkbox-group">
        <label><input type="checkbox" name="materials[]" value="white_gold"> Bia?e z?oto</label>
        <label><input type="checkbox" name="materials[]" value="yellow_gold"> ???te z?oto</label>
        <label><input type="checkbox" name="materials[]" value="platinum"> Platyna</label>
    </div>
    
    <input type="file" name="inspirations[]" multiple accept="image/*">
    <textarea name="inspiration_notes" placeholder="Notatki do inspiracji"></textarea>
    
    <button type="submit">Wy?lij projekt</button>
</form>
```

### Submit via REST API

```javascript
fetch('/wp-json/bdwp/v1/custom-design/submit', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        name: 'Jan Kowalski',
        email: 'jan@example.com',
        phone: '+48123456789',
        project_type: 'ring',
        brief: 'Chcia?bym zaprojektowa?...',
        budget: 5000,
        deadline: '2025-12-31',
        materials: ['white_gold', 'platinum'],
        stones: ['diamond'],
        inspiration_notes: 'Minimalistyczny styl...'
    })
})
.then(response => response.json())
.then(data => {
    console.log('Project ID:', data.project_id);
    // Redirect to thank you page
});
```

**Response**:
```json
{
    "success": true,
    "project_id": 123,
    "message": "Projekt zosta? pomy?lnie z?o?ony"
}
```

## Admin Management

### Project List

**Location**: Admin > Projekty na zam?wienie

**Columns**:
- Title
- Customer (name + email)
- Status (badge with color)
- Budget
- Date

**Filters**:
- By status
- By date
- Search by customer

### Quick Actions

From list view, quickly change status:
- ? Concept Ready
- ? CAD Approved
- ? In Production
- ? Ready for Pickup

Click action to update status and trigger email.

### Project Edit Screen

#### Customer Information
- Name
- Email
- Phone

#### Project Details
- Type (ring, necklace, etc.)
- Brief (full description)
- Budget
- Preferred deadline

#### Materials & Preferences
- Material checkboxes
- Stone checkboxes

#### Files
- Inspiration images (gallery)
- Concept files (gallery)
- CAD files (file upload)
- Completion photos (gallery)

#### Status Meta Box (Sidebar)
- Current status dropdown
- Status history timeline
- "Notify customer" checkbox
- Save button

## Email Notifications

### Intake Email

**Trigger**: Project submission (status: brief_received)

**Template**: `/woocommerce/emails/custom-design-intake.php`

**Subject**: "Dzi?kujemy za zg?oszenie projektu #{project_number}"

**Content**:
- Thank you message
- Project summary
- Next steps
- Contact information

### Status Update Email

**Trigger**: Status change (concept_ready, cad_approved, ready_for_pickup)

**Template**: `/woocommerce/emails/custom-design-update.php`

**Subject**: "Aktualizacja projektu #{project_number} - {status_label}"

**Content**:
- Status update message
- Specific instructions per status
- Download links (if applicable)
- Contact for questions

**Example Messages**:

**concept_ready**:
"Your design concepts are ready for review. Please check attached files and provide feedback."

**cad_approved**:
"CAD design approved! Your piece is now entering production. Estimated completion: [date]"

**ready_for_pickup**:
"Your custom jewelry is ready! Please visit our atelier or arrange delivery."

## Customer Dashboard

### My Projects Page

**URL**: `/moje-konto/custom-projects`

**Display**:
- List of customer's projects
- Status badges
- Submission date
- Link to details

### Project Detail View

**Elements**:
- Full project information
- Status timeline (visual)
- Uploaded files/images
- Comment section
- Contact button

### Comments/Communication

Customers can add comments:

```javascript
fetch('/wp-json/bdwp/v1/custom-design/123/comments', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify({
        comment: 'I love the design, can we adjust the stone size?'
    })
})
```

## REST API Endpoints

### Submit Project

**POST** `/wp-json/bdwp/v1/custom-design/submit`

**Parameters**: See submission section above.

### Get Project Status

**GET** `/wp-json/bdwp/v1/custom-design/{id}`

**Response**:
```json
{
    "project_id": 123,
    "title": "Projekt: ring - Jan Kowalski",
    "status": "concept_ready",
    "status_label": "Koncepcja gotowa",
    "status_history": [
        {
            "status": "brief_received",
            "timestamp": 1735660800,
            "user": "Admin"
        },
        {
            "status": "concept_ready",
            "timestamp": 1735747200,
            "user": "Designer"
        }
    ],
    "created_date": "2025-01-01T10:00:00+00:00"
}
```

### Add Comment

**POST** `/wp-json/bdwp/v1/custom-design/{id}/comments`

**Parameters**:
```json
{
    "comment": "Comment text"
}
```

## ACF Field Groups

### Group: Custom Project Details

**Location**: custom_project post type

**Fields**:

#### Customer
- `customer_name`: Text
- `customer_email`: Email
- `customer_phone`: Text

#### Project
- `project_type`: Select (ring, necklace, etc.)
- `project_brief`: Textarea
- `project_budget`: Number
- `deadline`: Date Picker

#### Preferences
- `preferred_material`: Checkbox (materials)
- `preferred_stones`: Checkbox (stones)

#### Files
- `inspiration_images`: Gallery
- `inspiration_notes`: Textarea
- `concept_files`: Gallery
- `cad_files`: File (multiple)
- `production_notes`: WYSIWYG
- `completion_photos`: Gallery

## Integration with Orders

When project is approved, optionally convert to WooCommerce order:

```php
// Create order from project
$order = wc_create_order();
$order->add_product( $custom_product, 1 );
$order->set_customer_id( $customer_id );
$order->add_meta_data( '_custom_project_id', $project_id );
$order->calculate_totals();
$order->save();
```

## File Management

### Uploads Organization

```
/uploads/custom-projects/
    /123/                      # Project ID
        /inspirations/         # Customer uploads
        /concepts/             # Designer concepts
        /cad/                  # CAD files
        /photos/               # Final product photos
```

### Security

- Files only accessible to:
  - Project author
  - Administrators
  - Shop managers

### File Validation

- Max size: 10 MB per file
- Allowed types: JPG, PNG, PDF
- Virus scanning (if plugin available)

## Reporting

### Admin Dashboard Widget

Shows:
- Total projects
- Projects by status
- Recent submissions

### Export Projects

```bash
# Export to CSV
wp custom-design export --status=all --format=csv > projects.csv
```

## Customization Examples

### Add Custom Status

```php
// Add to inc/custom-design/workflow.php
$statuses['design_revision'] = __( 'Revision Needed', 'bigdiamond-white-prestige' );
```

### Custom Email Template

Create `/woocommerce/emails/custom-design-revision.php`:

```php
<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>Your design requires some adjustments. Please review our notes below.</p>

<?php
do_action( 'woocommerce_email_footer', $email );
```

---

For complete file structure, see `FILE_STRUCTURE.md`.
