import { Component, signal } from '@angular/core';
import { provideRouter, RouterOutlet, Routes } from '@angular/router';
import { StandardCodeComponent } from './standard-code/standard-code.component';
import { CustomizeProduct } from './customize-product/customize-product';
import { CustomizationList } from './customization-list/customization-list';
import { NavbarComponent } from "./layout/navbar/navbar";


const routes: Routes = [
  { path: '', redirectTo: '/standard-code', pathMatch: 'full' },
  { path: 'standard-code', component: StandardCodeComponent },
  { path: 'customize-product/:id', component: CustomizeProduct },
  { path: 'customization-list', component: CustomizationList },
  { path: '**', redirectTo: '/standard-code' }
];

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, NavbarComponent],
  templateUrl: './app.html',
  styleUrls: ['./app.css']
})
export class App {
  protected readonly title = signal('frontend-customize-product');
}
