import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, Validators, ReactiveFormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {

  loading = false;
  errorMessage = '';
  successMessage = '';
  form;

  private apiUrl = 'http://localhost:8000/api/usuarios';

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private router: Router
  ) {
    this.form = this.fb.group({
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      role: ['empleado', Validators.required]
    });
  }

  submit() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading = true;
    this.errorMessage = '';
    this.successMessage = '';

    this.http.post(this.apiUrl, this.form.value).subscribe({
      next: () => {
        this.successMessage = 'Usuario creado correctamente';
        this.form.reset({ role: 'empleado' });
        this.loading = false;
      },
      error: err => {
        this.loading = false;

        if (err.status === 422) {
          this.errorMessage = 'Datos inválidos o email ya registrado';
        } else if (err.status === 403) {
          this.errorMessage = 'No tenés permisos para registrar usuarios';
        } else {
          this.errorMessage = 'Error inesperado al crear el usuario';
        }
      }
    });
  }
}
