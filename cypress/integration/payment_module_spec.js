describe('Payment Module Tests', () => {
  beforeEach(() => {
    cy.visit('http://localhost:8000/modules/payments/add.php');
  });

  it('should add and remove payment rows dynamically', () => {
    cy.get('#payment_details_table tbody tr.payment-row').should('have.length', 1);
    cy.get('.add-payment-row').click();
    cy.get('#payment_details_table tbody tr.payment-row').should('have.length', 2);
    cy.get('#payment_details_table tbody tr.payment-row').last().find('.remove-payment-row').click();
    cy.get('#payment_details_table tbody tr.payment-row').should('have.length', 1);
    cy.get('#payment_details_table tbody tr.payment-row').last().find('.remove-payment-row').should('be.disabled');
  });

  it('should toggle cheque number input visibility based on cheque/rtgs type', () => {
    cy.get('#payment_details_table tbody tr.payment-row').first().within(() => {
      cy.get('.cheque_rtgs_type').select('Cheque');
      cy.get('.cheque_number').should('be.visible');
      cy.get('.cheque_rtgs_type').select('RTGS');
      cy.get('.cheque_number').should('be.visible');
      cy.get('.cheque_rtgs_type').select('');
      cy.get('.cheque_number').should('not.be.visible');
    });
  });

  it('should show/hide partial and outstanding amount inputs based on full/partial selection', () => {
    cy.get('#payment_details_table tbody tr.payment-row').first().within(() => {
      cy.get('.payment_full_partial').select('Partial');
      cy.get('.partial_amount').should('be.visible');
      cy.get('.outstanding_amount').should('be.visible');
      cy.get('.payment_full_partial').select('Full');
      cy.get('.partial_amount').should('not.be.visible');
      cy.get('.outstanding_amount').should('not.be.visible');
    });
  });

  it('should calculate GST amounts correctly based on percentages and payment amount', () => {
    cy.get('#payment_details_table tbody tr.payment-row').first().within(() => {
      cy.get('.ptm_amount').clear().type('1000');
      cy.get('.cgst_percentage').clear().type('5');
      cy.get('.sgst_percentage').clear().type('5');
      cy.get('.igst_percentage').clear().type('0');
      cy.get('.cgst_amount').should('have.value', '50.00');
      cy.get('.sgst_amount').should('have.value', '50.00');
      cy.get('.igst_amount').should('have.value', '0.00');
    });
  });

  it('should update total payment amount when ptm_amount changes or rows are added/removed', () => {
    cy.get('#payment_details_table tbody tr.payment-row').first().within(() => {
      cy.get('.ptm_amount').clear().type('1000');
    });
    cy.get('.add-payment-row').click();
    cy.get('#payment_details_table tbody tr.payment-row').last().within(() => {
      cy.get('.ptm_amount').clear().type('500');
    });
    cy.get('#total_ptm_amount').should('have.value', '1500.00');
    cy.get('#payment_details_table tbody tr.payment-row').last().find('.remove-payment-row').click();
    cy.get('#total_ptm_amount').should('have.value', '1000.00');
  });

  it('should submit the form successfully', () => {
    cy.get('#pon_number').select(1);
    cy.get('#payment_details_table tbody tr.payment-row').first().within(() => {
      cy.get('.payment_type').select('Job Card');
      cy.get('.payment_entity').select(1);
      cy.get('.cheque_rtgs_type').select('Cheque');
      cy.get('.cheque_number').type('123456');
      cy.get('.pd_acc_number').type('12345');
      cy.get('.payment_full_partial').select('Full');
      cy.get('.ptm_amount').clear().type('1000');
      cy.get('.payment_invoice_date').type('2025-07-01');
    });
    cy.get('#submitBtn').click();
    cy.get('.toast-success').should('be.visible');
  });
});
