import { test, expect } from '@playwright/test';

test.describe('Excursion Creation Form', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to the excursion creation page
    await page.goto('/admin/excursions/new');
    
    // Wait for the page to load
    await page.waitForLoadState('networkidle');
  });

  test('should create an excursion with all fields', async ({ page }) => {
    // General Information
    await page.getByLabel('Catégorie').click();
    // Wait for searchable select to load
    await page.waitForTimeout(500);
    // Type to search or create
    const categoryInput = page.locator('.searchable-input').first();
    await categoryInput.fill('TEST_Category');
    await page.waitForTimeout(500);
    
    // Click on create option if available
    const createOption = page.locator('.searchable-create').first();
    if (await createOption.isVisible()) {
      await createOption.click();
    } else {
      // Select first result
      await page.locator('.searchable-result').first().click();
    }

    await page.getByLabel('Destination').click();
    await page.waitForTimeout(500);
    const destinationInput = page.locator('.searchable-input').nth(1);
    await destinationInput.fill('TEST_Destination');
    await page.waitForTimeout(500);
    
    const createOptionDest = page.locator('.searchable-create').nth(1);
    if (await createOptionDest.isVisible()) {
      await createOptionDest.click();
    } else {
      await page.locator('.searchable-result').first().click();
    }

    // Type
    await page.getByLabel('Type').selectOption('private');
    
    // Status
    await page.getByLabel('Statut').selectOption('active');
    
    // Best Seller
    await page.getByLabel('Best Seller').check();
    
    // Featured
    await page.getByLabel('Mis en avant').check();
    
    // Duration
    await page.getByLabel('Durée (heures)').fill('8');
    
    // Participants
    await page.getByLabel('Participants minimum').fill('1');
    await page.getByLabel('Participants maximum').fill('20');
    
    // Prices
    await page.getByLabel('Prix adulte').fill('120');
    await page.getByLabel('Prix enfant').fill('80');
    await page.getByLabel('Prix converti').fill('130');
    
    // Cancellation
    await page.getByLabel('Heures d\'annulation').fill('24');

    // Slug (auto-generated from French title, will fill after translations)
    
    // Click on Translations tab
    await page.getByRole('button', { name: '🌍 Traductions' }).click();
    await page.waitForTimeout(300);

    // French Translation
    await page.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[title]"]').fill('TEST_Excursion Djerba');
    await page.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[subtitle]"]').fill('Découverte de Djerba');
    await page.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[description]"]').fill('Description française de test automatique');
    await page.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[location]"]').fill('Djerba, Tunisie');
    await page.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[metaTitle]"]').fill('TEST Meta Title');
    await page.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[metaDescription]"]').fill('TEST Meta Description');

    // English Translation
    await page.locator('button.translation-tab[data-locale="en"]').click();
    await page.waitForTimeout(200);
    await page.locator('input[name*="[translations]"][name*="[locale][en]"][name*="[title]"]').fill('TEST_Djerba Excursion');
    await page.locator('textarea[name*="[translations]"][name*="[locale][en]"][name*="[subtitle]"]').fill('Djerba Discovery');
    await page.locator('textarea[name*="[translations]"][name*="[locale][en]"][name*="[description]"]').fill('English test description');

    // Italian Translation
    await page.locator('button.translation-tab[data-locale="it"]').click();
    await page.waitForTimeout(200);
    await page.locator('input[name*="[translations]"][name*="[locale][it]"][name*="[title]"]').fill('TEST_Escursione Djerba');
    await page.locator('textarea[name*="[translations]"][name*="[locale][it]"][name*="[subtitle]"]').fill('Scoperta di Djerba');
    await page.locator('textarea[name*="[translations]"][name*="[locale][it]"][name*="[description]"]').fill('Descrizione italiana di test');

    // Spanish Translation
    await page.locator('button.translation-tab[data-locale="es"]').click();
    await page.waitForTimeout(200);
    await page.locator('input[name*="[translations]"][name*="[locale][es]"][name*="[title]"]').fill('TEST_Excursión Djerba');
    await page.locator('textarea[name*="[translations]"][name*="[locale][es]"][name*="[subtitle]"]').fill('Descubrimiento de Djerba');
    await page.locator('textarea[name*="[translations]"][name*="[locale][es]"][name*="[description]"]').fill('Descripción española de test');

    // German Translation
    await page.locator('button.translation-tab[data-locale="de"]').click();
    await page.waitForTimeout(200);
    await page.locator('input[name*="[translations]"][name*="[locale][de]"][name*="[title]"]').fill('TEST_Djerba Ausflug');
    await page.locator('textarea[name*="[translations]"][name*="[locale][de]"][name*="[subtitle]"]').fill('Djerba entdecken');
    await page.locator('textarea[name*="[translations]"][name*="[locale][de]"][name*="[description]"]').fill('Deutsche Testbeschreibung');

    // Click on Itinerary tab
    await page.getByRole('button', { name: '📅 Itinéraire' }).click();
    await page.waitForTimeout(300);

    // Add first itinerary step
    await page.locator('button.add-collection[data-collection="itineraries"]').click();
    await page.waitForTimeout(300);
    
    const itinerary1 = page.locator('.collection-item[data-collection="itineraries"]').nth(0);
    await itinerary1.locator('input[name*="[stepNumber]"]').fill('1');
    await itinerary1.locator('input[name*="[time]"]').fill('09:00');
    await itinerary1.locator('input[name*="[duration]"]').fill('60');
    
    // Add French translation for itinerary 1
    await itinerary1.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await itinerary1.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[title]"]').fill('TEST_Départ hôtel');
    await itinerary1.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[description]"]').fill('Départ depuis l\'hôtel');

    // Add second itinerary step
    await page.locator('button.add-collection[data-collection="itineraries"]').click();
    await page.waitForTimeout(300);
    
    const itinerary2 = page.locator('.collection-item[data-collection="itineraries"]').nth(1);
    await itinerary2.locator('input[name*="[stepNumber]"]').fill('2');
    await itinerary2.locator('input[name*="[time]"]').fill('10:00');
    await itinerary2.locator('input[name*="[duration]"]').fill('120');
    
    // Add French translation for itinerary 2
    await itinerary2.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await itinerary2.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[title]"]').fill('TEST_Visite Djerba');
    await itinerary2.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[description]"]').fill('Visite des sites touristiques');

    // Click on Features tab
    await page.getByRole('button', { name: '✨ Caractéristiques' }).click();
    await page.waitForTimeout(300);

    // Add first feature
    await page.locator('button.add-collection[data-collection="features"]').click();
    await page.waitForTimeout(300);
    
    const feature1 = page.locator('.collection-item[data-collection="features"]').nth(0);
    await feature1.locator('input[name*="[icon]"]').fill('🚗');
    
    // Add French translation for feature 1
    await feature1.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await feature1.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[title]"]').fill('TEST_Transport confortable');
    await feature1.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[subtitle]"]').fill('Véhicule climatisé');

    // Add second feature
    await page.locator('button.add-collection[data-collection="features"]').click();
    await page.waitForTimeout(300);
    
    const feature2 = page.locator('.collection-item[data-collection="features"]').nth(1);
    await feature2.locator('input[name*="[icon]"]').fill('👨‍🏫');
    
    // Add French translation for feature 2
    await feature2.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await feature2.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[title]"]').fill('TEST_Guide professionnel');
    await feature2.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[subtitle]"]').fill('Accompagnement durant l\'excursion');

    // Click on Inclusions tab
    await page.getByRole('button', { name: '✅ Inclusions' }).click();
    await page.waitForTimeout(300);

    // Add inclusion
    await page.locator('button.add-collection[data-collection="inclusions"]').click();
    await page.waitForTimeout(300);
    
    const inclusion1 = page.locator('.collection-item[data-collection="inclusions"]').nth(0);
    await inclusion1.locator('select[name*="[type]"]').selectOption('included');
    await inclusion1.locator('input[name*="[icon]"]').fill('🚌');
    
    // Add French translation for inclusion
    await inclusion1.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await inclusion1.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[text]"]').fill('TEST_Transport aller-retour');

    // Add second inclusion
    await page.locator('button.add-collection[data-collection="inclusions"]').click();
    await page.waitForTimeout(300);
    
    const inclusion2 = page.locator('.collection-item[data-collection="inclusions"]').nth(1);
    await inclusion2.locator('select[name*="[type]"]').selectOption('included');
    await inclusion2.locator('input[name*="[icon]"]').fill('👨‍🏫');
    
    await inclusion2.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await inclusion2.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[text]"]').fill('TEST_Guide professionnel');

    // Click on FAQ tab
    await page.getByRole('button', { name: '❓ FAQ' }).click();
    await page.waitForTimeout(300);

    // Add FAQ
    await page.locator('button.add-collection[data-collection="faqs"]').click();
    await page.waitForTimeout(300);
    
    const faq1 = page.locator('.collection-item[data-collection="faqs"]').nth(0);
    await faq1.locator('input[name*="[position]"]').fill('1');
    await faq1.locator('input[name*="[isActive]"]').check();
    
    // Add French translation for FAQ
    await faq1.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await faq1.locator('input[name*="[translations]"][name*="[locale][fr]"][name*="[question]"]').fill('TEST_Combien de personnes peuvent participer ?');
    await faq1.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[answer]"]').fill('TEST_L\'excursion peut accueillir jusqu\'à 20 participants.');

    // Click on Badges tab
    await page.getByRole('button', { name: '🏷️ Badges' }).click();
    await page.waitForTimeout(300);

    // Add badge
    await page.locator('button.add-collection[data-collection="badges"]').click();
    await page.waitForTimeout(300);
    
    const badge1 = page.locator('.collection-item[data-collection="badges"]').nth(0);
    await badge1.locator('select[name*="[badgeType]"]').selectOption('popular');
    await badge1.locator('input[name*="[isActive]"]').check();
    
    // Add French translation for badge
    await badge1.locator('button.translation-tab[data-locale="fr"]').click();
    await page.waitForTimeout(200);
    await badge1.locator('textarea[name*="[translations]"][name*="[locale][fr]"][name*="[text]"]').fill('TEST_Populaire');

    // Submit the form
    await page.locator('button[type="submit"]').click();
    
    // Wait for response
    await page.waitForTimeout(2000);

    // Check for success message or error
    const successMessage = page.locator('.alert.alert-success');
    const errorMessage = page.locator('.alert.alert-error');
    
    if (await errorMessage.isVisible()) {
      const errorText = await errorMessage.textContent();
      console.error('Form submission error:', errorText);
      
      // Check for specific field errors
      const fieldErrors = page.locator('.invalid-feedback');
      const errorCount = await fieldErrors.count();
      console.log(`Number of field errors: ${errorCount}`);
      
      for (let i = 0; i < errorCount; i++) {
        const error = fieldErrors.nth(i);
        const errorText = await error.textContent();
        console.log(`Field error ${i + 1}:`, errorText);
      }
      
      throw new Error(`Form submission failed: ${errorText}`);
    }
    
    expect(await successMessage.isVisible()).toBeTruthy();
    console.log('Excursion created successfully!');
  });

  test('should show error when required fields are missing', async ({ page }) => {
    // Submit without filling required fields
    await page.locator('button[type="submit"]').click();
    await page.waitForTimeout(1000);

    // Check for error message
    const errorMessage = page.locator('.alert.alert-error');
    expect(await errorMessage.isVisible()).toBeTruthy();
    
    // Check for field errors
    const fieldErrors = page.locator('.invalid-feedback');
    expect(await fieldErrors.count()).toBeGreaterThan(0);
  });
});
