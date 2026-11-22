# Fresh (فريش) Split AC — Error Codes & Common Faults (Egypt)

## How Fresh splits display faults; product lines; OEM platform

### Takeaway
Fresh sells standard, "Smart Inverter" (plasma) and Smart Plus inverter splits, plus duct, concealed and floor-standing units, in Egypt. The official product page mentions "Self Diagnosis" but publishes no code table. I found no public source that names the OEM platform.

### Cited Findings
- Fresh's own store (be.fresh.com.eg) lists "Smart Inverter 1.5 HP Cool – Plasma" (SKU 500016381) with inverter, a plasma ionizer filter, self-clean, R410a, a 5-year warranty on both units, and a "Self Diagnosis" feature. It gives no codes, no manual download and no hotline. Confidence: high (official page) — [Fresh official store](https://be.fresh.com.eg/fresh-air-conditioner-smart-inverter-1-5-hp-cool-plasma.html)
- Fresh also sells a Duct Inverter 3 HP cool/hot unit — [Fresh official store](https://be.fresh.com.eg/fresh-air-conditioner-duct-inverter-3-hp-cool-hot.html)
- Retail model codes include SIFW13C/IP–SIFW13C/O-X2 (1.5 HP Smart Inverter, cool only), SIFW20H/IP–SIFW20H/O-X2 (2.25 HP Smart Inverter Plasma, cool/heat), SPIFW20HIP (Smart Plus inverter 2.25 HP), SIFW24H/O-X4 (3 HP smart inverter plasma), and X-SIFW24H/IP-AG R32 (3 HP inverter on R32) — [2B](https://2b.com.eg/en/fresh-air-conditioner-2-25-hp-cooling-heating-smart-inverter-plasma-sifw20h-ip-sifw20h-o-x2.html); [Halan](https://shop.halan.com/en/Fresh-Air-Conditioner-225-HP-Cool-and-Heating-Smart-Plus-inverter-SPIFW20HIP-SPIFW20HOP/67f3c5d7686076f02be15e8a/p/2B); [Kaza](https://kaza.com.eg/en/fresh-air-condition-3-hp-smart-cooling-heating-inverter-plasma-white-sifw24h-o-x4.html); [2B R32](https://2b.com.eg/en/fresh-air-conditioner-3-hp-cooling-heating-inverter-white-x-sifw24h-ip-ag-r32-x-sifw24h-o-x3-r32.html)
- B.Tech lists Fresh floor-standing ("free stair") 5 HP and concealed 5 HP inverter units. Its 3 HP listing mentions "ECO Sleep Mode" — [B.Tech](https://btech.com/en/p/fresh-air-conditioner-3-hp-cooling-heating-sifw24h-o-x4); [B.Tech concealed](https://btech.com/en/p/fresh-concealed-air-conditioner-5hp-cool-heat-inverter-silver-500009259500009258)
- In this research, "Smart" in Fresh model names showed no confirmed Wi-Fi or app control in the listings I checked — [search summary of retailer pages, 2B/Kaza/B.Tech above]
- freshegypt.com is a parked domain that redirects to a GoDaddy for-sale page. It is not an official source (observed directly on 2026-11-22).

### Inferences
- The indoor unit likely shows codes on its LED display panel ("Self Diagnosis"). The exact format (Ex / Fx / Hx / LED blink count) is not confirmed. Confidence: low.
- The OEM platform (Gree, Midea, Chigo, AUX or another) is not established by any source I found. Do not state one in articles. Confidence: none.

### Gaps
- No official Fresh user manual or service manual for splits was found online.
- I found no source for a Wi-Fi or app model, or for how app alerts show faults.

## Known error codes and meanings

### Takeaway
I found no Fresh-specific error-code table from an official, technician or Arabic source. Generic codes such as E1, E4, E5, F1 and H6 mean different things in different brands' tables. Writing "on Fresh, E1 = X" would be fabrication.

### Cited Findings
- E1 differs by brand: "High Pressure Protection" in one table, a T1 sensor open/short in another, and indoor/outdoor communication error in Bosch's table — [Scribd generic codes](https://www.scribd.com/document/504053991/Error-Codes); [Bosch](https://www.bosch-thermotechnology.com/global/en/commercial-industrial/service/after-sales-services/air-conditioning-and-ventilation/error-codes/)
- E5 = indoor heat-exchanger sensor fault on Samsung, but an evaporator sensor fault on AEG portables — [Samsung AE](https://www.samsung.com/ae/support/home-appliances/check-out-the-displayed-error-codes-on-samsung-indoor-unit-air-conditioner); [AEG](https://aeg.co.uk/support/support-articles/air-comfort/air-conditioners/portable-air-conditioner-displays-code-a5-or-e5)
- Both Arabic and English searches for Fresh-specific code lists returned only other brands (Samsung, AUX, Xiaomi, Carrier) — searches run 2026-11-22; e.g. [AUX list](https://www.scribd.com/document/869416744/Errorcode-AUX)

### Inferences
- For articles, describe code categories rather than meanings: sensor faults, communication between indoor and outdoor units, protection (pressure, current, temperature), and fan motor. Tell readers to read the code off the display, note the model number on the indoor label (for example SIFW13C/IP), and call a technician or Fresh service. Confidence: medium as general guidance, and it is not Fresh-specific.

### Gaps
- No sourced meaning for any Fresh code (E0–E9, F-series, H-series, P-series).
- Unknown whether inverter and non-inverter Fresh lines share a table.
- The Fresh service hotline number was not found on the product page. Get it from the site owner or the official Contact page.

## Remote/receiver issues and common non-coded faults

### Takeaway
I found no Fresh-specific source. Only general split-AC knowledge applies, and it should be labelled as such.

### Cited Findings
- Fresh markets self-clean and a plasma ionizer filter on Smart Inverter models, so filter and indoor-coil cleanliness are relevant to their maintenance — [Fresh official store](https://be.fresh.com.eg/fresh-air-conditioner-smart-inverter-1-5-hp-cool-plasma.html)
- Not every displayed alert is a fault. For example, Samsung's CF code is a filter-clean reminder. This is a cross-brand example, not a Fresh one — [Samsung AE Arabic](https://www.samsung.com/ae_ar/support/home-appliances/error-codes-on-samsung-indoor-air-conditioner)

### Inferences (general split-AC knowledge, not Fresh-sourced; low–medium confidence)
- Remote doesn't respond: weak or dead batteries, a blocked or dirty IR receiver window on the indoor unit, strong direct sunlight or LED light on the receiver, or a remote lock. If the indoor unit beeps on other commands, the receiver is fine.
- Weak cooling: dirty filter or coil, low refrigerant from a leak, a dirty outdoor condenser, an undersized unit, or high outdoor heat in the Egyptian summer.
- Water dripping indoors: blocked drain line or an unlevel indoor unit.
- Bad smell: mould on the coil or filter.
- Noise or vibration: loose mounting or fan imbalance.
- Outdoor unit won't start: voltage problems (common in Egypt), a capacitor (non-inverter), or the inverter board.

### Gaps
- No Fresh-specific troubleshooting guide was found to corroborate these.
