/*
  Gruppe: 13
  Mitglieder:  Daniel Menzel,Rohullah Sediqi, Tesch Etienne Mathis
  Beleg: Weihnachtsgeschenkeshop
*/

import { ref, computed } from 'vue';

// cart liegt ausserhalb der funktion = globaler state (singleton).
// so bleiben die daten erhalten wenn man die ansicht wechselt.
const cart = ref([]);

export function useCart() {
  
  // funktion zum hinzufügen
  const addToCart = (product) => {
    // erst mal checken ob lagerbestand da ist
    if (product.stock <= 0) return "ausverkauft";

    // schauen ob das produkt schon im Warenkorb ist
    const item = cart.value.find(i => i.id === product.id);
    const currentQty = item ? item.qty : 0;

    // man darf nicht mehr reinlegen als da ist
    if (currentQty + 1 > product.stock) {
      return "limit_reached"; 
    }

    if (item) {
      item.qty++;
    } else {
      // objekt kopieren und mit menge 1 reinpacken
      cart.value.push({ ...product, qty: 1 });
    }
    return "success";
  };

  const removeFromCart = (index) => {
    cart.value.splice(index, 1);
  };

  // logik für die +/- buttons
  const updateCartQty = (item, change) => {
    const newQty = item.qty + change;

    // beim erhöhen muss das limit gecheckt werden
    if (change > 0) {
      if (newQty > item.stock) {
        return false; // fehler zurückgeben
      }
    }

    item.qty = newQty;

    // wenn menge 0 ist, artikel rauswerfen
    if (item.qty <= 0) {
      cart.value = cart.value.filter(i => i.id !== item.id);
    }
    
    return true; 
  };

  // gesamtpreis berechnen
  const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0);
  });

  // mwst anteil 7%
  const vatAmount = computed(() => {
    return cartTotal.value * 0.07; 
  });

  // anzahl aller items für den header bubble
  const totalItems = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.qty, 0);
  });

  return { 
    cart, 
    addToCart, 
    removeFromCart, 
    updateCartQty, 
    cartTotal, 
    vatAmount, 
    totalItems 
  };
}