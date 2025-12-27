import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class StandardCodeService {
  private apiUrl = 'http://127.0.0.1:8000/api/standard-codes';

  constructor(private http: HttpClient) {}
  
  /** Get all standard codes */
  getStandardCodes(): Observable<string[]> {
    return this.http.get<any[]>(this.apiUrl).pipe(
      map((res: any[]) => res.map(item => item.code)) // assuming Laravel returns {id, code}
    );
  }


  /** Add a new standard code */
  addStandardCode(code: string): Observable<any> {
    return this.http.post(this.apiUrl, { code });
  }
}
