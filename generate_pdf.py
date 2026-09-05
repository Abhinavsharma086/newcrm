import markdown
from reportlab.lib.pagesizes import landscape, letter
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak, ListFlowable, ListItem
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_LEFT

def create_pdf(filename):
    doc = SimpleDocTemplate(filename, pagesize=landscape(letter),
                            rightMargin=40, leftMargin=40,
                            topMargin=40, bottomMargin=40)

    styles = getSampleStyleSheet()
    title_style = ParagraphStyle(
        name="TitleStyle",
        parent=styles['Heading1'],
        alignment=TA_CENTER,
        fontSize=32,
        spaceAfter=40,
        textColor="#1a365d"
    )
    
    subtitle_style = ParagraphStyle(
        name="SubtitleStyle",
        parent=styles['Normal'],
        alignment=TA_CENTER,
        fontSize=18,
        spaceAfter=20,
        textColor="#4a5568"
    )
    
    heading_style = ParagraphStyle(
        name="SlideHeading",
        parent=styles['Heading2'],
        alignment=TA_LEFT,
        fontSize=24,
        spaceAfter=20,
        textColor="#2b6cb0"
    )
    
    body_style = ParagraphStyle(
        name="BodyText",
        parent=styles['Normal'],
        fontSize=14,
        leading=20,
        spaceAfter=12
    )

    code_style = ParagraphStyle(
        name="CodeStyle",
        parent=styles['Code'],
        fontSize=12,
        leading=16,
        leftIndent=20,
        textColor="#2d3748",
        backColor="#edf2f7"
    )

    bullet_style = ParagraphStyle(
        name="BulletText",
        parent=styles['Normal'],
        fontSize=14,
        leading=22,
        leftIndent=20
    )

    Story = []

    # Slide 1: Title
    Story.append(Spacer(1, 100))
    Story.append(Paragraph("Havells ERP+CRM", title_style))
    Story.append(Paragraph("Enterprise Resource Planning & Customer Relationship Management System", subtitle_style))
    Story.append(Paragraph("Streamlining Business Operations, Inventory, and Vendor Management", subtitle_style))
    Story.append(PageBreak())

    # Slide 2: Problem Statement
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Problem Statement", heading_style))
    items = [
        "<b>Fragmented Data:</b> Tracking sales, inventory, and vendor POs across disparate systems leads to inefficiency.",
        "<b>Manual Data Entry:</b> Extracting bill info manually is time-consuming and prone to human error.",
        "<b>Limited Visibility:</b> Lack of a unified dashboard for admin and employees hampers quick decision-making.",
        "<b>Complex Operations:</b> Managing societies, contractors, leads, quotations, and ticketing is chaotic without a unified workflow."
    ]
    for item in items:
        Story.append(Paragraph(f"• {item}", bullet_style))
    Story.append(PageBreak())

    # Slide 3: Tech Stack
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Tech Stack", heading_style))
    Story.append(Paragraph("<b>Frontend:</b> Laravel Blade Templates, TailwindCSS v4 with Vite, Axios", body_style))
    Story.append(Spacer(1, 10))
    Story.append(Paragraph("<b>Backend:</b> PHP 8.2 & Laravel 12, Spatie Permissions & ActivityLog, DomPDF", body_style))
    Story.append(Spacer(1, 10))
    Story.append(Paragraph("<b>Database & Hosting:</b> MySQL, Database Queue/Session", body_style))
    Story.append(PageBreak())

    # Slide 4: Architecture
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Architecture / System Design", heading_style))
    items = [
        "<b>Frontend Interface:</b> Users interact with the Laravel Web App built with Blade and Tailwind.",
        "<b>Business Logic:</b> Laravel Controllers handle the core business logic.",
        "<b>Database:</b> Data is stored and retrieved using Eloquent ORM interacting with a MySQL Database.",
        "<b>External Services:</b> Includes OCR Parsing Service for GST Bills and DomPDF for generating invoices and quotations."
    ]
    for item in items:
        Story.append(Paragraph(f"• {item}", bullet_style))
    Story.append(PageBreak())

    # Slide 5: Key Features
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Key Features", heading_style))
    items = [
        "<b>Comprehensive Dashboard:</b> Admin & Employee portals with Role-Based Access Control (RBAC).",
        "<b>Vendor PO & Invoice Management:</b> Create, track, and manage vendor purchase orders and invoices.",
        "<b>Smart OCR Integration:</b> Auto-parse GST bills (photos) to auto-fill PO details.",
        "<b>Inventory & Warehouse Management:</b> Stock reconciliation, stock-in/out, and transactions logging.",
        "<b>CRM & Ticketing:</b> Manage leads, customers, appointments, and support tickets.",
        "<b>Accounting:</b> Journal entries, balance sheet, and profit-loss reports."
    ]
    for item in items:
        Story.append(Paragraph(f"• {item}", bullet_style))
    Story.append(PageBreak())

    # Slide 6: Code Highlights
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Code Highlights", heading_style))
    Story.append(Paragraph("<b>OCR Bill Parsing Logic (VendorPoController.php):</b>", body_style))
    code = '''
public function ocrParseBill(Request $request) {
    // Validates uploaded image/PDF
    // Mocked extraction simulating an OCR integration for GST invoices
    $extractedData = [
        'vendor_name' => 'Proxima Piping Systems',
        'subtotal' => 35.71,
        'grand_total' => 42.00,
        // ... Parses item details, GST percent, HSN codes
    ];
    // Auto-creates supplier if not found
    $vendor = Supplier::firstOrCreate(['name' => $extractedData['vendor_name']]);
    return response()->json(['success' => true, 'data' => $extractedData]);
}
    '''
    Story.append(Paragraph(code.replace(' ', '&nbsp;').replace('\n', '<br/>'), code_style))
    Story.append(Spacer(1, 10))
    Story.append(Paragraph("<i>Automatically populates complex PO forms from an uploaded bill.</i>", body_style))
    Story.append(PageBreak())

    # Slide 7: Database Schema Overview
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Database Schema Overview", heading_style))
    items = [
        "<b>Users & Auth:</b> users, roles, permissions, activity_log",
        "<b>CRM Core:</b> customers, leads, societies, appointments",
        "<b>Inventory:</b> products, warehouses, stock_transactions, material_logs",
        "<b>Sales & Operations:</b> quotations, invoices, tasks, tickets, shipments",
        "<b>Accounts:</b> accounts, journal_entries, payments, credit_debit_notes",
        "<b>Vendors:</b> contractors, suppliers, vendor_pos, vendor_invoices"
    ]
    for item in items:
        Story.append(Paragraph(f"• {item}", bullet_style))
    Story.append(PageBreak())

    # Slide 8: Challenges & Solutions
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Challenges & Solutions", heading_style))
    items = [
        "<b>Challenge:</b> Creating complex POs from supplier bills is highly error-prone.<br/><b>Solution:</b> Implemented OCR endpoint that auto-reads GST bills and maps items, HSN codes, and taxes to the PO creation form.",
        "<b>Challenge:</b> Managing multi-tenant/branch data and diverse user roles (Admin vs. Employee).<br/><b>Solution:</b> Integrated spatie/laravel-permission, assigned branch-specific filters, and implemented strict middleware routing.",
        "<b>Challenge:</b> Complex tax and accounting reconciliation.<br/><b>Solution:</b> Built custom journal entry controllers and double-entry accounting tables linked directly to invoices."
    ]
    for item in items:
        Story.append(Paragraph(f"• {item}", bullet_style))
        Story.append(Spacer(1, 5))
    Story.append(PageBreak())

    # Slide 9: Future Scope
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Future Scope", heading_style))
    items = [
        "<b>Real OCR Integration:</b> Connect the mocked OCR endpoint to AWS Textract or Google Cloud Vision API for dynamic real-world invoice reading.",
        "<b>AI-Powered Analytics:</b> Use LLMs to generate insights from sales trends and customer tickets.",
        "<b>Mobile Application:</b> A native React Native / Flutter app for field agents (contractors/employees) to manage appointments and upload bills on the go.",
        "<b>Automated Tax Filing:</b> Direct API integration with government GST portals."
    ]
    for item in items:
        Story.append(Paragraph(f"• {item}", bullet_style))
    Story.append(PageBreak())

    # Slide 10: Conclusion
    Story.append(Spacer(1, 20))
    Story.append(Paragraph("Conclusion", heading_style))
    items = [
        "<b>Havells ERP+CRM</b> is a robust, end-to-end platform tailored for scalable enterprise operations.",
        "By combining <b>inventory tracking, automated accounting, and OCR-assisted data entry</b>, it significantly reduces operational overhead.",
        "Built on modern <b>Laravel 12</b> and <b>TailwindCSS</b>, it is fast, secure, and ready for future integrations."
    ]
    for item in items:
        Story.append(Paragraph(f"• {item}", bullet_style))
    
    Story.append(Spacer(1, 40))
    Story.append(Paragraph("<b>Thank You!</b>", subtitle_style))

    doc.build(Story)

if __name__ == "__main__":
    create_pdf("Havells_ERP_CRM_Presentation.pdf")
    print("PDF generated successfully.")
