/*
  Gruppe: 13
  Mitglieder:  Daniel Menzel,Rohullah Sediqi, Tesch Etienne Mathis
  Beleg: Weihnachtsgeschenkeshop
*/

import { ref, onMounted } from 'vue';

export function useProducts() {
  const products = ref([]);
  const error = ref(null);

  // produkte von der php api laden
  const fetchProducts = async () => {
    try {
      const baseUrl = import.meta.env.VITE_API_BASE_URL;
      const res = await fetch(baseUrl + 'products.php');
      
      if (!res.ok) throw new Error("API fehler");
      
      products.value = await res.json();
    } catch (e) {
      error.value = "fehler beim laden: " + e.message;
    }
  };

  // lagerbestand update an api senden
  const updateProductStock = async (product) => {
    const baseUrl = import.meta.env.VITE_API_BASE_URL;
    try {
      const res = await fetch(baseUrl + 'update_stock.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: product.id, stock: product.stock })
      });
      return res.ok;
    } catch (e) {
      return false;
    }
  };

  // direkt beim start laden
  onMounted(fetchProducts);

  return { products, error, fetchProducts, updateProductStock };
}