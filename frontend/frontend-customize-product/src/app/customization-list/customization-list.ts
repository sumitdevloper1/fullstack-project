import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { CustomizationService } from '../services/customization-service';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { FilterCustomizationsPipe } from '../pipes/filter-customizations-pipe';
import * as XLSX from 'xlsx';
import { saveAs } from 'file-saver';
import { FilterByColumnsPipe } from '../pipes/filter-by-columns-pipe';

@Component({
  selector: 'app-customization-list',
  standalone: true,
  imports: [
    CommonModule,
    HttpClientModule,
    RouterModule,
    FormsModule,
    FilterCustomizationsPipe,
    FilterByColumnsPipe,
  ],
  templateUrl: './customization-list.html',
  styleUrls: ['./customization-list.css'],
})
export class CustomizationList implements OnInit {

  customizations: any[] = [];
  displayCustomizations: any[] = [];
  isLoading = false;
  errorMessage = '';
  searchTerm: string = '';

  // ✅ MULTI DELETE SELECTION
  selectedIds = new Set<number>();

  filters: any = {
    unique_code: '',
    created_at: '',
    standard_code_id: '',
    standard_code: '',
    mark: '',
    print: '',
    engraving: '',
    added: '',
    removed: '',
    note: '',
    capacity: '',
    neck: '',
    item_name: '',
    item_description: '',
    remarks: '',
    vendor_name: '',
    pack_size: '',
    moq: '',
  };

  constructor(
    private customizationService: CustomizationService,
    private http: HttpClient
  ) {}

  ngOnInit(): void {
    this.fetchCustomizations();
  }

  // ================= FILTER =================
  clearFilters() {
    Object.keys(this.filters).forEach(key => {
      this.filters[key] = '';
    });
  }

  // ================= FETCH =================
  fetchCustomizations() {
    this.isLoading = true;
    this.errorMessage = '';
    this.searchTerm = '';

    this.customizationService.getAllCustomizations().subscribe({
      next: (res: any) => {
        this.customizations = res.data || res;
        this.displayCustomizations = this.customizations.map(item =>
          this.formatCustomization(item)
        );

        // ✅ clear selections after reload
        this.selectedIds.clear();
        this.isLoading = false;
      },
      error: (err: any) => {
        console.error(err);
        this.errorMessage = 'Failed to load customizations!';
        this.isLoading = false;
      },
    });
  }

  refreshList() {
    this.fetchCustomizations();
  }

  // ================= FORMAT =================
  formatCustomization(item: any) {
    const formattedItem = { ...item };

    // --- Printing Mark ---
    try {
      const markJson = JSON.parse(item.printing_color_mark_json || '{}');
      formattedItem.printing_color_mark_text = markJson
        ? `${markJson.shape || ''} | ${markJson.location || ''} | ${markJson.color || ''}`
            .replace(/\s*\|\s*\|\s*/g, '')
        : '—';
    } catch {
      formattedItem.printing_color_mark_text = '—';
    }

    // --- Printing Print ---
    try {
      const printJson = JSON.parse(item.printing_color_print_json || '{}');
      formattedItem.printing_color_print_text = printJson
        ? `${printJson.customText || ''} | ${printJson.printLocation || ''} | ${printJson.printColor || ''}`
            .replace(/\s*\|\s*\|\s*/g, '')
        : '—';
    } catch {
      formattedItem.printing_color_print_text = '—';
    }

    // --- Accessories ---
    formattedItem.add_accessories_list = this.parseAccessories(item.add_accessories_data);
    formattedItem.add_accessories_text = this.makeAccessoryText(formattedItem.add_accessories_list);

    formattedItem.remove_accessories_list = this.parseAccessories(item.remove_accessories_data);
    formattedItem.remove_accessories_text = this.makeAccessoryText(formattedItem.remove_accessories_list);

    formattedItem.showFullAdd = false;
    formattedItem.showFullRemove = false;

    return formattedItem;
  }

  parseAccessories(data: any): any[] {
    try {
      if (!data) return [];
      if (typeof data === 'string') {
        const parsed = JSON.parse(data);
        return Array.isArray(parsed) ? parsed : [parsed];
      }
      return Array.isArray(data) ? data : [data];
    } catch {
      return [];
    }
  }

  makeAccessoryText(list: any[]): string {
    if (!list || list.length === 0) return '—';
    return list.map(a => {
      if (typeof a === 'string') return a;
      if (a?.name && a?.code) return `${a.name} (${a.code})`;
      if (a?.name) return a.name;
      return JSON.stringify(a);
    }).join(', ');
  }

  // ================= MULTI SELECT =================
  toggleSelection(id: number, event: any) {
    event.target.checked
      ? this.selectedIds.add(id)
      : this.selectedIds.delete(id);
  }

  toggleSelectAll(event: any) {
    if (event.target.checked) {
      this.displayCustomizations.forEach(item =>
        this.selectedIds.add(item.id)
      );
    } else {
      this.selectedIds.clear();
    }
  }

  isAllSelected(): boolean {
    return (
      this.displayCustomizations.length > 0 &&
      this.selectedIds.size === this.displayCustomizations.length
    );
  }

  // ================= DELETE =================
  deleteCustomization(id: number): void {
    if (!confirm('Are you sure you want to delete this customization?')) return;

    this.http.delete(`http://127.0.0.1:8000/api/customizations/${id}`)
      .subscribe({
        next: () => {
          alert('✅ Deleted successfully');
          this.refreshList();
        },
        error: () => alert('❌ Delete failed'),
      });
  }

  deleteSelected(): void {
    if (this.selectedIds.size === 0) {
      alert('Please select records to delete');
      return;
    }

    if (!confirm(`Delete ${this.selectedIds.size} selected customizations?`)) return;

    this.http.post(
      'http://127.0.0.1:8000/api/customizations/bulk-delete',
      { ids: Array.from(this.selectedIds) }
    ).subscribe({
      next: () => {
        alert('✅ Selected records deleted');
        this.selectedIds.clear();
        this.refreshList();
      },
      error: () => alert('❌ Bulk delete failed'),
    });
  }

  // ================= EXCEL =================
  exportToExcel() {
    if (!this.displayCustomizations.length) {
      alert('No data available');
      return;
    }

    const exportData = this.displayCustomizations.map((item, i) => ({
      '#': i + 1,
      'Unique Code': item.unique_code,
      'Standard Code ID': item.standard_code_id,
      'Standard Code': item.standard_code?.code || '',
      'Printing (Mark)': item.printing_color_mark_text,
      'Printing (Print)': item.printing_color_print_text,
      Engraving: item.engraving || '',
      'Accessories Added': item.add_accessories_text,
      'Accessories Removed': item.remove_accessories_text,
      'Label Note': item.specifications?.[0]?.note || '',
      Capacity: item.specifications?.[0]?.capacity || '',
      'Neck Size': item.specifications?.[0]?.neck_size || '',
      'Item Name': item.specifications?.[0]?.item_name || '',
      'Item Description': item.specifications?.[0]?.item_description || '',
      Remarks: item.specifications?.[0]?.remarks || '',
      'Vendor Name': item.specifications?.[0]?.vendor_name || '',
      'Pack Size': item.specifications?.[0]?.pack_size || '',
      MOQ: item.specifications?.[0]?.moq || '',
    }));

    const worksheet = XLSX.utils.json_to_sheet(exportData);
    const workbook = { Sheets: { Data: worksheet }, SheetNames: ['Data'] };
    const buffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
    saveAs(new Blob([buffer]), 'customizations.xlsx');
  }
}
