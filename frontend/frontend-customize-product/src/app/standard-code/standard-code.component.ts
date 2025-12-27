import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Router, RouterLink } from '@angular/router';
import { StandardCodeService } from '../services/standard-code.service';

declare global {
  interface Window {
    bootstrap?: any;
  }
}

interface Toast {
  message: string;
  type: 'success' | 'danger' | 'info' | 'warning';
}

@Component({
  selector: 'app-standard-code',
  standalone: true,
  imports: [FormsModule, CommonModule, HttpClientModule,],
  templateUrl: './standard-code.component.html',
  styleUrls: ['./standard-code.component.css']
})
export class StandardCodeComponent implements OnInit {
  standardCodes: { id: number; code: string }[] = [];
  filteredStandardCodes: { id: number; code: string }[] = [];
  selectedStandardCode: number | null = null;
  newCode: string = '';
  searchText: string = '';
  showSuggestions: boolean = false;
  toasts: Toast[] = []; // store toast messages

  @ViewChild('createModal') createModal!: ElementRef;
  @ViewChild('excelInput') excelInput!: ElementRef;

  constructor(
    private standardCodeService: StandardCodeService,
    private http: HttpClient,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadStandardCodes();
  }

  loadStandardCodes(): void {
    this.standardCodeService.getStandardCodes().subscribe({
      next: (codes: string[]) => {
        this.standardCodes = codes.map((code, index) => ({ id: index + 1, code }));
        this.filteredStandardCodes = [...this.standardCodes];
        console.log('Loaded standard codes:', this.standardCodes);
      },
      error: (err) => console.error('Error loading codes:', err)
    });
  }

  onSearchChange(): void {
    const query = this.searchText.toLowerCase();
    if (!query) {
      this.filteredStandardCodes = [];
      this.showSuggestions = false;
      return;
    }

    this.filteredStandardCodes = this.standardCodes.filter(item =>
      item.code.toLowerCase().includes(query)
    );
    this.showSuggestions = this.filteredStandardCodes.length > 0;
  }

  selectSuggestion(codeItem: { id: number; code: string }): void {
    this.selectedStandardCode = codeItem.id;
    this.searchText = codeItem.code;
    this.showSuggestions = false;
  }

  saveNewCode(): void {
    if (!this.newCode.trim()) return;

    this.standardCodeService.addStandardCode(this.newCode).subscribe({
      next: () => {
        this.newCode = '';
        this.loadStandardCodes();

        const modalEl: any = this.createModal.nativeElement;
        const modal = window.bootstrap?.Modal.getInstance(modalEl);
        modal?.hide();

        this.showToast('Standard code saved successfully!', 'success');
      },
      error: (err) => {
        console.error('Error saving code:', err);
        this.showToast('Error saving code: It may already exist.', 'danger');
      }
    });
  }

  openModal(): void {
    const modalEl: any = this.createModal.nativeElement;
    const modal = new window.bootstrap.Modal(modalEl);
    modal.show();
  }

  onSubmit(_: any): void {
    if (!this.selectedStandardCode) return;
    console.log('Navigating with standard code ID:', this.selectedStandardCode);
    this.router.navigate(['/customize-product', this.selectedStandardCode]);
  }

  hideSuggestions(): void {
    setTimeout(() => (this.showSuggestions = false), 200);
  }
 // 📂 Handle Excel import
  onFileChange(event: any): void {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    this.http.post('http://127.0.0.1:8000/api/customizations/import', formData)
      .subscribe({
        next: (response: any) => {
          console.log('Import success:', response);
          this.showToast('✅ Excel imported successfully!', 'success');
          this.loadStandardCodes();
        },
        error: (error: any) => {
          console.error('Import failed:', error);
          this.showToast('❌ Failed to import Excel file.', 'danger');
        }
      });

    // Reset file input after upload
    this.excelInput.nativeElement.value = '';
  }

  showToast(message: string, type: 'success' | 'danger' | 'info' | 'warning' = 'info') {
    const newToast: Toast = { message, type };
    this.toasts.push(newToast);
    setTimeout(() => this.removeToast(newToast), 5000);
  }

  removeToast(toast: Toast) {
    this.toasts = this.toasts.filter(t => t !== toast);
  }
}
