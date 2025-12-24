/**
 * HMS Invoice PDF Generation
 * 
 * Uses jsPDF to generate PDF invoices
 */

jQuery(document).ready(function($) {
    // Download PDF button click handler
    $('#download-pdf-btn').on('click', function(e) {
        e.preventDefault();
        
        // Show loading state
        $(this).prop('disabled', true).text('Generating PDF...');
        
        // Load jsPDF library if not already loaded
        if (typeof jsPDF === 'undefined') {
            $.getScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js')
                .done(function() {
                    generateInvoicePDF();
                })
                .fail(function() {
                    alert('Failed to load PDF generation library. Please try again.');
                    $('#download-pdf-btn').prop('disabled', false).text('Download as PDF');
                });
        } else {
            generateInvoicePDF();
        }
    });
    
    function generateInvoicePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Get invoice data from the page
        const hospitalName = $('h1').text().trim();
        const invoiceNumber = $('#invoice-number').text().trim();
        const billDate = $('#bill-date').text().trim();
        const patientName = $('#patient-name').text().trim();
        const patientAddress = $('#patient-address').text().trim();
        const patientContact = $('#patient-contact').text().trim();
        
        // Add custom font for better support (optional)
        doc.addFont('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.66/fonts/Roboto/Roboto-Regular.ttf', 'Roboto', 'normal');
        doc.setFont('Roboto');
        
        // Set font sizes
        doc.setFontSize(20);
        doc.text(hospitalName, 105, 20, { align: 'center' });
        
        doc.setFontSize(16);
        doc.text('INVOICE', 105, 30, { align: 'center' });
        
        doc.setFontSize(12);
        doc.text(`Invoice Number: ${invoiceNumber}`, 20, 50);
        doc.text(`Date: ${billDate}`, 20, 60);
        
        doc.text('Bill To:', 20, 80);
        doc.setFontSize(11);
        doc.text(patientName, 20, 90);
        doc.text(patientAddress, 20, 100);
        doc.text(patientContact, 20, 110);
        
        // Add table headers
        doc.setFontSize(12);
        doc.text('Description', 20, 130);
        doc.text('Quantity', 100, 130);
        doc.text('Price', 130, 130);
        doc.text('Total', 170, 130);
        
        // Add line
        doc.line(20, 135, 190, 135);
        
        // Get bill items from the table
        let yPosition = 145;
        let hasItems = false;
        
        // Try multiple selectors to find bill items
        $('.invoice-body table tbody tr, #bill-items-table tbody tr, table tbody tr').each(function() {
            const $row = $(this);
            const cells = $row.find('td');
            
            // Skip if this is a "No services found" row
            const rowText = $row.text().toLowerCase();
            if (rowText.includes('no services') || rowText.includes('not found')) {
                return;
            }
            
            // Try different column configurations
            let sNo = '', description = '', quantity = '', price = '', total = '';
            
            if (cells.length >= 5) {
                // Standard format: S.No, Description, Quantity, Unit Price, Total
                sNo = $(cells[0]).text().trim();
                description = $(cells[1]).text().trim();
                quantity = $(cells[2]).text().trim();
                price = $(cells[3]).text().trim();
                total = $(cells[4]).text().trim();
            } else if (cells.length >= 4) {
                // Alternative format: Description, Quantity, Price, Total
                description = $(cells[0]).text().trim();
                quantity = $(cells[1]).text().trim();
                price = $(cells[2]).text().trim();
                total = $(cells[3]).text().trim();
            }
            
            // Only add if we have meaningful data
            if (description && quantity && price) {
                hasItems = true;
                doc.setFontSize(10);
                
                // Truncate long descriptions
                if (description.length > 40) {
                    description = description.substring(0, 37) + '...';
                }
                
                // Add S.No if available
                if (sNo) {
                    doc.text(sNo, 20, yPosition);
                    doc.text(description, 30, yPosition);
                    doc.text(quantity, 110, yPosition);
                    doc.text(price, 140, yPosition);
                    doc.text(total, 170, yPosition);
                } else {
                    doc.text(description, 20, yPosition);
                    doc.text(quantity, 110, yPosition);
                    doc.text(price, 140, yPosition);
                    doc.text(total, 170, yPosition);
                }
                
                yPosition += 10;
                
                // Check if we're running out of space
                if (yPosition > 240) {
                    doc.addPage();
                    yPosition = 20;
                }
            }
        });
        
        // If no items found, add a placeholder
        if (!hasItems) {
            doc.setFontSize(10);
            doc.text('No items found in invoice. Please check the invoice page.', 20, yPosition);
            yPosition += 20;
        }
        
        // Add totals
        doc.line(20, yPosition, 190, yPosition);
        yPosition += 10;
        
        doc.setFontSize(12);
        
        // Try multiple selectors to get totals
        let subtotal = $('#subtotal-amount').text().trim() ||
                      $('#subtotal').text().trim() ||
                      $('.invoice-totals tr:eq(0) td:last').text().trim() || '0.00';
        
        let discount = $('#discount-amount').text().trim() ||
                      $('#discount').text().trim() ||
                      $('.invoice-totals tr:eq(1) td:last').text().trim() || '0.00';
        
        let tax = $('#tax-amount').text().trim() ||
                 $('#tax').text().trim() ||
                 $('.invoice-totals tr:eq(2) td:last').text().trim() || '0.00';
        
        let total = $('#total-amount').text().trim() ||
                   $('#total-amount').text().trim() ||
                   $('.invoice-totals tr:eq(3) td:last').text().trim() || '0.00';
        
        let paid = $('#paid-amount').text().trim() ||
                 $('#amount-paid').text().trim() ||
                 $('.invoice-totals tr:eq(4) td:last').text().trim() || '0.00';
        
        let balance = $('#balance-amount').text().trim() ||
                    $('#balance-due').text().trim() ||
                    $('.invoice-totals tr:eq(5) td:last').text().trim() || total;
        
        // Clean up the values (remove currency symbols, etc.)
        subtotal = subtotal.replace(/[^\d.-]/g, '');
        discount = discount.replace(/[^\d.-]/g, '');
        tax = tax.replace(/[^\d.-]/g, '');
        total = total.replace(/[^\d.-]/g, '');
        paid = paid.replace(/[^\d.-]/g, '');
        balance = balance.replace(/[^\d.-]/g, '');
        
        doc.text(`Subtotal: ${subtotal}`, 130, yPosition);
        yPosition += 10;
        
        if (discount && discount !== '0.00' && discount !== '0') {
            doc.text(`Discount: -${discount}`, 130, yPosition);
            yPosition += 10;
        }
        
        if (tax && tax !== '0.00' && tax !== '0') {
            doc.text(`Tax: ${tax}`, 130, yPosition);
            yPosition += 10;
        }
        
        doc.setFontSize(14);
        doc.text(`Total: ${total}`, 130, yPosition);
        yPosition += 10;
        
        doc.setFontSize(12);
        doc.text(`Paid: ${paid}`, 130, yPosition);
        yPosition += 10;
        
        doc.setFontSize(14);
        doc.text(`Balance Due: ${balance}`, 130, yPosition);
        
        // Add footer
        yPosition = 270;
        doc.setFontSize(10);
        doc.text('Thank you for your business!', 105, yPosition, { align: 'center' });
        yPosition += 10;
        doc.text('This is a computer-generated invoice and does not require a signature.', 105, yPosition, { align: 'center' });
        
        // Save the PDF
        const filename = `Invoice_${invoiceNumber.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
        doc.save(filename);
        
        // Reset button state
        $('#download-pdf-btn').prop('disabled', false).text('Download as PDF');
    }
});