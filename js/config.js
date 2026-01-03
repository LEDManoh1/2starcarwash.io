const defaultConfig = {
  background_color: "#f5f7fa",
  surface_color: "#ffffff",
  text_color: "#0f2027",
  primary_action_color: "#ef4444",
  secondary_action_color: "#3b82f6",
  font_family: "Segoe UI",
  font_size: 16,
  company_name: "2 Star Car Wash & Services",
  tagline: "Professional Car Wash & Auto Care in Tanzania",
  service_heading: "Our Premium Services",
  booking_heading: "Book Your Service",
  gallery_heading: "Our Work",
  testimonial_heading: "What Our Customers Say",
  location_text: "Dar es Salaam, Tanzania",
  phone_number: "+255 123 456 789",
  whatsapp_number: "+255 123 456 789",
  hours_text: "Mon-Sat: 8AM-6PM, Sun: 9AM-4PM"
};

// expose config and service list/globals for pages to share
window.defaultConfig = defaultConfig;
window.SERVICES = [
  { value: 'Exterior Wash', label: 'Exterior Wash - TZS 10,000', price: 10000 },
  { value: 'Full Body Wash', label: 'Full Body Wash - TZS 15,000', price: 15000 },
  { value: 'Engine Wash', label: 'Engine Wash - TZS 20,000', price: 20000 },
  { value: 'Interior Vacuum', label: 'Interior Vacuum - TZS 12,000', price: 12000 },
  { value: 'Premium Package', label: 'Premium Package - TZS 35,000', price: 35000 },
  { value: 'Seat Cleaning', label: 'Seat Cleaning - TZS 18,000', price: 18000 },
  { value: 'Underbody Wash', label: 'Underbody Wash - TZS 15,000', price: 15000 },
  { value: 'Wax & Polish', label: 'Wax & Polish - TZS 22,000', price: 22000 },
  { value: 'Interior Detailing', label: 'Interior Detailing - TZS 25,000', price: 25000 }
];

window.SERVICE_PRICES = window.SERVICES.reduce((acc, s) => (acc[s.value] = s.price, acc), {});

window.getServiceLabel = function(value){
  const svc = (window.SERVICES||[]).find(s=>s.value===value);
  return svc ? svc.label : value;
};

