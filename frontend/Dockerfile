# ── Build stage ──────────────────────────────────────────────────────────────
FROM python:3.11-slim AS builder

WORKDIR /app

# Install dependencies into an isolated prefix so we can copy only what's
# needed into the final image, keeping it lean.
COPY requirements.txt .
RUN pip install --upgrade pip \
 && pip install --prefix=/install --no-cache-dir -r requirements.txt

# ── Runtime stage ─────────────────────────────────────────────────────────────
FROM python:3.11-slim

# Create a non-root user for security
RUN addgroup --system appgroup && adduser --system --ingroup appgroup appuser

WORKDIR /app

# Pull in only the installed packages from the build stage
COPY --from=builder /install /usr/local

# Copy application source
COPY . .

# Hand ownership to the non-root user
RUN chown -R appuser:appgroup /app

USER appuser

EXPOSE 8000

# Bind to 0.0.0.0 so Railway's proxy can reach the container, and use
# a single worker per replica (scale horizontally via Railway replicas).
CMD ["gunicorn", "--bind", "0.0.0.0:8000", "--workers", "2", "--threads", "2", "--timeout", "60", "app:app"]
