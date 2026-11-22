# Midea residential split AC error codes and faults (Egypt / Miraco), research notes

Confidence scale: HIGH = a service manual table, read directly. MED = a third-party technician site that copies manufacturer tables. LOW = blog, forum or comment.
Important caveat: I found **no Egypt-specific (Mission / Miraco) code table online**. Every code below comes from the international Midea platform tables. Writers must tell readers that the meaning depends on the model and that they should check their own unit's manual.

## 1. How Midea splits show faults, and which families sell in Egypt

### Takeaway
Midea Egypt sells the "Mission Inverter" line (cool-only and heat-pump, R410a). Its official page only says the LCD "shows control functions and error code". On the Midea-style platform, faults show as a 2-character code on the indoor display, a pattern of flashes on the operation lamp plus the timer lamp, and LEDs on the outdoor IPM board. A hidden remote-control routine lets a technician read more diagnostic values.

### Cited Findings
- Midea Egypt lists "Mission Inverter High Wall" in Heat Pump and Cool Only versions. The 1.5 HP heat pump has product code 46307694 and uses R410a. The page mentions a "Smart LCD display which shows control functions and error code" but lists no codes. — [Midea Egypt, Mission Inverter HP](https://www.midea.com/eg/air-conditioners/high-wall-air-conditioners/mission-inverter-high-wall-air-conditioners-heat-pump-inverter); [Cool Only](https://www.midea.com/eg/air-conditioners/high-wall-air-conditioners/mission-inverter-high-wall-air-conditioners-cool-only-inverter.46307706); [Arabic page](https://www.midea.com/eg-ar/air-conditioners/high-wall-air-conditioners/mission-inverter-high-wall-air-conditioners-heat-pump-inverter) (HIGH for product facts)
- Display method on the indoor unit: on an error "the operation lamp will flash in a corresponding series, the timer lamp may turn on or begin flashing, and an error code will be displayed". E codes = timer lamp OFF. F codes = timer lamp ON. P codes = timer lamp FLASH. The number of operation-lamp flashes gives the code number. — [Durastar single-zone service manual, pp.72](https://res.cloudinary.com/ldi/image/upload/v1615159221/Durastar/Durastar_Single_Zone_Service_Manual.pdf) (HIGH for that platform. See the provenance caveat in the Gaps)
- Outdoor IPM board: a green LED and a red LED flash in combinations that map to indoor codes P0, P1, E5 and P4. — same manual, p.15 (HIGH)
- Remote "Information Inquiry" (technician mode): press LED 3 times, then SWING 3 times, within 10 s. The unit beeps for 2 s. Then the LED and SWING buttons step through values: T1 room, T2 evaporator coil, T3 condenser coil, T4 outdoor, TP discharge, and an "ST" compressor-stop cause (0–99). — same manual, pp.20–21 (HIGH, platform-specific)
- A garbled code or a code not in the manual may just be a temperature reading. If the unit responds to the remote, suspect the display board. If it does not respond, suspect the indoor PCB. — same manual, p.73 (HIGH)
- Third-party compilations split Midea codes by series ("R Series", "9V", "9A", plus outdoor models). The same code means different things in different series. — [removeandreplace.com](https://removeandreplace.com/2016/06/11/midea-air-conditioner-error-codes-indoor-and-outdoor-models) (MED)

### Inferences
- Mission is probably a Midea-platform wall split. Its exact code table could not be confirmed. Articles should present the table as "common on Midea inverter splits" rather than claim it is official for Mission.

### Gaps
- No Miraco/Midea Egypt service manual or support page with codes was found. I could not confirm an "Xtreme" family on midea.com/eg. I found only the Mission listings.
- I could not verify whether Egyptian units have the LED/SWING inquiry function.

## 2. Common E / F / P codes and their meanings

### Takeaway
On the Midea inverter-split table read directly from a service manual, the meanings are as follows. E0 means indoor EEPROM error. E1 means indoor/outdoor communication error. E3 means indoor fan speed abnormal. E4/E5 mean room/coil sensor faults. EC means refrigerant leak detected. The F codes are outdoor sensors, outdoor EEPROM and outdoor fan. The P codes are inverter/IPM/voltage protections. Other Midea series reuse the same codes with different meanings (especially E1–E7 and P1–P6).

### Cited Findings: main table (Durastar single-zone, Midea-style platform, HIGH)
Source: [Durastar Single Zone Service Manual, p.72](https://res.cloudinary.com/ldi/image/upload/v1615159221/Durastar/Durastar_Single_Zone_Service_Manual.pdf)

| Code | Op-lamp flashes / timer lamp | Meaning |
|---|---|---|
| E0 | 1 / OFF | Indoor unit EEPROM parameter error |
| E1 | 2 / OFF | Indoor/outdoor communication error |
| E3 | 4 / OFF | Indoor fan speed outside normal range |
| E4 | 5 / OFF | Room sensor T1 open/short |
| E5 | 6 / OFF | Evaporator coil sensor T2 open/short |
| EC | 7 / OFF | Refrigerant leak detected |
| F0 | 1 / ON | Overload current protection |
| F1 | 2 / ON | Outdoor ambient sensor T4 open/short |
| F2 | 3 / ON | Condenser coil sensor T3 open/short |
| F3 | 4 / ON | Discharge temp sensor TP open/short |
| F4 | 5 / ON | Outdoor unit EEPROM error |
| F5 | 6 / ON | Outdoor fan speed outside normal range |
| P0 | 1 / FLASH | IPM malfunction or IGBT over-strong current protection |
| P1 | 2 / FLASH | Over-voltage or too-low-voltage protection |
| P2 | 3 / FLASH | IPM module high-temperature protection |
| P3 | 4 / FLASH | Outdoor ambient too low (heating; below −25 °C for 1 h, so not relevant in Egypt) |
| P4 | 5 / FLASH | Inverter compressor drive error |
| P5 | 6 / FLASH | Indoor mode conflict (multi-zone only) |
| P6 | 7 / FLASH | Low/high pressure protection (36k model) or IPM high temperature (<36k) |

- Detection logic for EC: within the first 8 minutes after the compressor starts, the T2 coil temperature does not drop as expected while the frequency runs above 50 Hz. If this happens 3 times, the unit shows "EC" and turns off. Suspected causes: T2 sensor, indoor PCB, leak or blockage. — same manual, p.89 (HIGH)
- E1 logic: the indoor unit gets no feedback from the outdoor unit for 110 s, 4 consecutive times. Parts to suspect: indoor PCB, outdoor PCB, reactor. The check covers the S–N signal voltage and the outdoor wiring. — same manual, p.84 (HIGH)

### Cited Findings: series where the same codes mean something else (MED)
Source: [removeandreplace.com, Midea indoor/outdoor codes](https://removeandreplace.com/2016/06/11/midea-air-conditioner-error-codes-indoor-and-outdoor-models)
- R series indoor: E0 EEPROM. E1 indoor/outdoor communication. E2 zero-crossing signal error. E3 indoor fan speed out of control. **E5 outdoor temperature sensor** (conflicts with the T2 meaning above). E6 room/evaporator sensor. P0 IGBT over-current. P1 over/under-voltage. P2 compressor top temperature. P4 inverter compressor drive.
- 9V/9A series: same as R, plus E7 outdoor fan speed, **P4 compressor position protection** and P5 inverter module protection.
- Some outdoor-board displays (multi-split outdoor models) use different meanings: E0 EEPROM, E4 outdoor sensor, E5 compressor voltage, E7 outdoor main chip ↔ compressor chip communication, P0 discharge/top temperature, P1/P2 high/low pressure (only certain models), P3 compressor current, P4 inverter module, P6 condenser high temperature.
- A general search summary found E1 described elsewhere as "high pressure protection" or "inverter compressor drive error" on other Midea tables. — [search compilation incl. Scribd / Arlington](https://arlingtonairconditioningheating.com/midea-air-conditioner-error-codes/) (LOW. Do not use)

### Inferences
- Codes that are **stable across sources**: E0 = EEPROM, E1 = indoor/outdoor communication (in most split tables), P0 = IPM/IGBT over-current, P1 = voltage protection. **Model-dependent**: E2–E7, P2, P4–P6, and all F codes.

### Gaps
- Provenance of the Durastar manual: one consumer blog claims Durastar is made by Gree. No source confirmed Midea manufacture ([thetradetable](https://thetradetable.com/a/on-the-table/who-makes-durastar-mini-split), LOW). The code structure (EC leak, F/P with lamp patterns) matches the Midea tables, but treat the table as a "Midea-platform example, unverified OEM".
- I found no official midea.com service manual for a Mission model. I recommend asking the site owner or a technician to photograph the code table from a Mission manual.
- EH/EL/"CL"/"FL" etc. were not covered by any source found.

## 3. Protection codes vs real failures; communication errors

### Takeaway
P codes are protective shutdowns triggered by current, voltage or temperature limits. They can come from external conditions such as Egyptian grid voltage swings or a dirty condenser in heat. They can also signal a failing IPM board or compressor. E1 is the classic communication fault, often caused by wiring.

### Cited Findings
- "The inverter module has an automatic shutoff mechanism based on the unit's current, voltage, and temperature". When it triggers, the code shows and the unit stops. — [Durastar SM p.17](https://res.cloudinary.com/ldi/image/upload/v1615159221/Durastar/Durastar_Single_Zone_Service_Manual.pdf) (HIGH)
- Other built-in protections: a 3-minute compressor restart delay; shutoff if discharge temperature exceeds 115 °C for 5 s; shutoff and a code if the indoor fan stays below 300 RPM. — same, p.17 (HIGH)
- Sensor redundancy: if one sensor fails, the unit keeps running and shows the code ("emergency use"). If more than one fails, it stops. — same, p.17 (HIGH)
- The first step in every troubleshooting flow is "Power off, then restart the unit after 2 minutes". If the problem goes away, the unit is normal. — same, pp.84, 89 (HIGH)
- The quick-replacement table lists many parts for the P codes: IPM board, compressor, reactor, refrigerant, fan motors, pressure protectors. A P code does not point to one part by itself. — same, p.80 (HIGH)

### Inferences
- Consumer advice: a 2-minute power reset is reasonable. A recurring P0/P1/P4 or E1 needs a technician. Repeated P1 may point to supply voltage, so mention a stabilizer/voltage check as something a technician measures (no specific numbers).

### Gaps
- No Egypt-specific data on the frequency of voltage-related faults.

## 4. Common faults without a code, and their causes

### Takeaway
The service manual's no-code table lists power/connection issues, remote batteries, dirty filters and condenser fins, wrong mode or setpoint, Silence mode, blocked airflow, open doors and windows, and heavy heat load. Field causes include capacitors, contactors, low voltage, sensor placement and refrigerant pressure problems.

### Cited Findings (all from [Durastar SM pp.76–78](https://res.cloudinary.com/ldi/image/upload/v1615159221/Durastar/Durastar_Single_Zone_Service_Manual.pdf), HIGH)
- Unit won't start: power failure, tripped breaker, loose connections, voltage too high or low, dead remote batteries, broken remote.
- Air not cold, or weak cooling: dirty air filter, dirty condenser fins, setpoint above room temperature, Fan mode selected, SILENCE function on, ambient too hot, heavy heat load, doors or windows open, inlet or outlet blocked.
- Unit runs then stops, or cycles often: voltage, the heat-load conditions above, blocked airflow. Field causes: compressor overload short-cycling, high or low discharge or suction pressure.
- Noise: loose hold-down bolts or screws, shipping plates still attached, compressor noise.
- Field causes, electrical: blown fuse/varistor, shorted or open capacitor, faulty contactor, faulty room sensor or a sensor in the wrong place, shorted or grounded compressor or fan motor, faulty louver stepping motor.
- Odd behaviour from "interference from cell phone towers and remote boosters". Remedy: power-cycle. (p.77)

### Gaps
- Water leaking from the indoor unit (drain blockage) is not in the extracted table. The owner's manual mentions drain hose installation only as an installation item (p.~1–2). Find a source before writing about it.
