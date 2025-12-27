import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { CustomizationService } from '../services/customization-service';
import { FilterPipe } from '../pipes/filter-customizations-pipe';

@Component({
  selector: 'app-customize-product',
  standalone: true,
  imports: [FormsModule, CommonModule, HttpClientModule, RouterLink, FilterPipe],
  templateUrl: './customize-product.html',
  styleUrls: ['./customize-product.css'],
})
export class CustomizeProduct implements OnInit {
  standardCode: string = '';

  printing = {
    shape: '',
    location: '',
    color: '',
    customText: '',
    printLocation: '',
    printColor: '',
  };

  engraving = {
    enable: false,
    text: '',
  };

  labelSpec = {
    file: null as File | null,
    note: '',
    neckSize: '',
    capacity: '',
    item_name: '',
    item_description: '',
    remarks: '',
    vendor_name: '',
    pack_size: '',
    moq: '',
  };


  // ✅ Accessories structure (supports ID + name or custom name)
  accessories = {
    add: [] as { id: number | null; name: string }[],
    remove: [] as { id: number | null; name: string }[],
  };

  // ✅ Accessories UI
  showAccessories = false;
  searchAdd = '';
  searchRemove = '';
  newAddAccessory = '';
  newRemoveAccessory = '';

  accessoriesList = [
    { id: 1, name: 'Joint Clip, PP, 14/23' },
    { id: 2, name: 'Joint Clip, PP, 19/26' },
    { id: 3, name: 'Joint Clip, PP, 24/29' },
    { id: 4, name: 'Joint Clip, PP, 29/32' },
    { id: 5, name: 'Joint Clip, PP, 34/35' },
    {
      id: 6,
      name: 'Caps, PP Screw Cap with Pouring Ring, Blue, GL-45 for Laboratory Reagent Bottles',
    },
    {
      id: 7,
      name: 'Caps, PP Screw Cap with Pouring Ring, Orange, GL-45 for Laboratory Reagent Bottles',
    },
    {
      id: 8,
      name: 'Caps, PP Screw Cap with Pouring Ring, Yellow, GL-45 for Laboratory Reagent Bottles',
    },
    {
      id: 9,
      name: 'Caps, PP Screw Cap with Pouring Ring, Green, GL-45 for Laboratory Reagent Bottles',
    },
    {
      id: 10,
      name: 'Caps, PP Screw Cap with Pouring Ring, Grey, GL-45 for Laboratory Reagent Bottles',
    },
    {
      id: 11,
      name: 'Caps, PP Screw Cap with Pouring Ring, Blue, GL-25 for Laboratory Reagent Bottles',
    },
    {
      id: 12,
      name: 'Caps, PP Screw Cap with Pouring Ring, Blue, GL-32 for Laboratory Reagent Bottles',
    },
    { id: 13, name: 'Screw Thread Cap, Red, Straight, GL-14' },
    { id: 14, name: 'Plastic Hose Connectors, White, Straight, GL-14' },
  ];

  constructor(private customizationService: CustomizationService, private route: ActivatedRoute) {}

  ngOnInit(): void {
    this.standardCode = this.route.snapshot.paramMap.get('id') || '';
    console.log('Route param id:', this.standardCode);
  }

  // ✅ Toggle Accessories section visibility
  toggleAccessoriesSection() {
    this.showAccessories = !this.showAccessories;
  }

  // ✅ Check if accessory is selected
  isAccessorySelected(list: { id: number | null; name: string }[], id: number) {
    return list.some((x) => x.id === id);
  }
  resetAccessories() {
    this.accessories = { add: [], remove: [] };
    this.newAddAccessory = '';
    this.newRemoveAccessory = '';
    this.searchAdd = '';
    this.searchRemove = '';
  }

  // ✅ Handle checkbox toggle
  toggleAccessory(item: { id: number; name: string }, type: 'add' | 'remove', event: any) {
    const checked = event.target.checked;
    const list = this.accessories[type];

    if (checked) {
      if (!list.some((x) => x.id === item.id)) list.push(item);
    } else {
      this.accessories[type] = list.filter((x) => x.id !== item.id);
    }
  }

  // ✅ Add custom typed accessory (when no dropdown is used)
  addCustomAccessory(type: 'add' | 'remove') {
    const newName = type === 'add' ? this.newAddAccessory.trim() : this.newRemoveAccessory.trim();
    if (!newName) return;

    const list = this.accessories[type];
    if (!list.some((x) => x.name.toLowerCase() === newName.toLowerCase())) {
      list.push({ id: null, name: newName });
    }

    if (type === 'add') this.newAddAccessory = '';
    else this.newRemoveAccessory = '';
  }

  // ✅ File Upload
  onFileChange(event: any) {
    if (event.target.files && event.target.files.length > 0) {
      this.labelSpec.file = event.target.files[0];
    }
  }

  // ✅ Hash generator
  // simpleHash(str: string): string {
  //   let hash = 0,
  //     i,
  //     chr;
  //   if (str.length === 0) return hash.toString();
  //   for (i = 0; i < str.length; i++) {
  //     chr = str.charCodeAt(i);
  //     hash = (hash << 5) - hash + chr;
  //     hash |= 0;
  //   }
  //   return Math.abs(hash).toString();
  // }

// ✅ Small hash (base36)
// shortHash(str: string): string {
//   let hash = 0;
//   for (let i = 0; i < str.length; i++) {
//     hash = (hash << 5) - hash + str.charCodeAt(i);
//     hash |= 0;
//   }
//   return Math.abs(hash).toString(36).toUpperCase().substring(0, 4);
// }

  // ✅ Unique code generation
  // generateCustomizationCode(): string {
  //   const parts = [
  //     this.standardCode,
  //     this.printing.shape,
  //     this.printing.location,
  //     this.printing.color,
  //     this.printing.customText,
  //     this.printing.printLocation,
  //     this.printing.printColor,
  //     this.engraving.enable ? 'ENGRAVE' : 'NOENGRAVE',
  //     this.engraving.text,
  //     this.labelSpec.note,
  //     this.labelSpec.file ? this.labelSpec.file.name : '',
  //     JSON.stringify(this.accessories.add),
  //     JSON.stringify(this.accessories.remove),
  //   ].join('|');
  //   // const codeString = parts.join('|');
  //   // return this.simpleHash(codeString);
  //    return `${this.standardCode}-${this.shortHash(parts)}`;
  // }

  


  // ✅ Save all customization data
saveAllInfo() {

  const printingColorMarkJson = JSON.stringify({
    shape: this.printing.shape || null,
    location: this.printing.location || null,
    color: this.printing.color || null,
  });

  const printingColorPrintJson = JSON.stringify({
    customText: this.printing.customText || null,
    printLocation: this.printing.printLocation || null,
    printColor: this.printing.printColor || null,
  });

  const engravingText = this.engraving.enable ? this.engraving.text : '';
  const hasSpecification =
  this.labelSpec.note?.trim() || this.labelSpec.file ? 'yes' : 'no';

  const formData = new FormData();
formData.append('standard_code_id', String(this.standardCode));
  formData.append('printing_color_mark_json', printingColorMarkJson);
  formData.append('printing_color_print_json', printingColorPrintJson);
  formData.append('engraving', engravingText);
  formData.append('is_specification', hasSpecification);

  // ✅ Accessories
  formData.append('add_accessories_data', JSON.stringify(this.accessories.add));
  formData.append('remove_accessories_data', JSON.stringify(this.accessories.remove));

  // ✅ Specifications
  formData.append('specifications[0][note]', this.labelSpec.note || '');
  formData.append('specifications[0][neck_size]', this.labelSpec.neckSize || '');
  formData.append('specifications[0][capacity]', this.labelSpec.capacity || '');

  formData.append('specifications[0][item_name]', this.labelSpec.item_name || '');
  formData.append('specifications[0][item_description]', this.labelSpec.item_description || '');
  formData.append('specifications[0][remarks]', this.labelSpec.remarks || '');
  formData.append('specifications[0][vendor_name]', this.labelSpec.vendor_name || '');
  formData.append('specifications[0][pack_size]', this.labelSpec.pack_size || '');
  formData.append('specifications[0][moq]', this.labelSpec.moq || '');

  if (this.labelSpec.file) {
    formData.append(
      'specifications[0][file]',
      this.labelSpec.file,
      this.labelSpec.file.name
    );
  }

  this.customizationService.saveCustomization(formData).subscribe({
    next: (res: any) => {
      // ✅ BACKEND GENERATED UNIQUE CODE
      alert('✅ Saved Successfully. Unique Code: ' + res.unique_code);
    },
    error: (err) => {
      alert('❌ Failed to save customization' + (err.error?.message ? ': ' + err.error.message : ''));
      console.error(err);
    },
  });
}

  resetPrinting() {
    this.printing = {
      shape: '',
      location: '',
      color: '',
      customText: '',
      printLocation: '',
      printColor: '',
    };
  }
}
