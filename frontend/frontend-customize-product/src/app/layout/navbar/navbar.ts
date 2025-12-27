import { Component } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './navbar.html',
  styleUrls: ['./navbar.css']
})

export class NavbarComponent {
  constructor(private router: Router) { }
   logout() { 
    localStorage.clear(); // remove token
   this.router.navigate(['/login']); } 
}