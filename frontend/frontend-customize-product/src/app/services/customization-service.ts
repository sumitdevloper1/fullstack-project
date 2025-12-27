import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
// export class CustomizationService {
//   getAllCustomizations() {
//     throw new Error('Method not implemented.');
//   }
//   private apiUrl = 'http://127.0.0.1:8000/api/customizations/';

//   constructor(private http: HttpClient) {}

//   // Send JSON string payload to Laravel
//   saveCustomization(payload: any): Observable<any> {
//     return this.http.post(this.apiUrl, payload);
//   }
// }

export class CustomizationService {
  private apiUrl = 'http://127.0.0.1:8000/api/customizations/';

  constructor(private http: HttpClient) {}

  getAllCustomizations(): Observable<any> {
    return this.http.get(this.apiUrl);
  }

  saveCustomization(payload: any): Observable<any> {
    return this.http.post(this.apiUrl, payload);
  }
}