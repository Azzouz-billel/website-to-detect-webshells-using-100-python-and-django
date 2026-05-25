import numpy as np
from layer import Dense
from activations import Relu, Sigmoid

class Model:
    def __init__(self, network=None):
        self.network = network if network is not None else []

    @classmethod
    def build_deep_factory(cls, input_dim=256, hidden_depth=40, hidden_width=64):
        """
        Programmatically constructs a network with more than 40 hidden layers.
        Prevents manual dimension mismatches and enables Depth Sensitivity Analysis.
        """
        network_layers = []

        # First layer: input features -> first hidden width
        network_layers.append(Dense(input_dim, hidden_width))
        network_layers.append(Relu())

        # Stack (hidden_depth - 1) additional hidden layers
        for _ in range(hidden_depth - 1):
            network_layers.append(Dense(hidden_width, hidden_width))
            network_layers.append(Relu())

        # Output layer: hidden width -> single binary output
        network_layers.append(Dense(hidden_width, 1))
        network_layers.append(Sigmoid())

        return cls(network_layers)

    def predict(self, input_data):
        # FIX: ensure input is always 2D (batch, features) regardless of
        # whether a single sample (shape (256,)) or a batch is passed in.
        output = np.atleast_2d(input_data)
        for layer in self.network:
            output = layer.forward(output)
        return output

    def predict_class(self, input_data):
        res = self.predict(input_data)
        return int((res >= 0.5).flatten()[0])

    def predict_all(self, input_data):
        return [self.predict_class(input_data[i]) for i in range(len(input_data))]

    def accuracy(self, input_data, input_real_data):
        n = len(input_data)
        predicted = self.predict_all(input_data)
        # FIX: flatten real labels to scalars before comparing
        real = [int(np.array(input_real_data[i]).flatten()[0]) for i in range(n)]
        correct = sum(p == r for p, r in zip(predicted, real))
        return correct / n

    def fit(self, x_train, y_train, loss, loss_prime,
            epochs=1000, learning_rate=0.01, verbose=True, beta=0.9):
        errors = []
        for e in range(epochs):
            output = self.predict(x_train)
            error  = loss(y_train, output)
            grad   = loss_prime(y_train, output)

            for layer in reversed(self.network):
                grad = layer.backward(grad, learning_rate, beta)

            errors.append(error)
            if verbose and (e + 1) % 100 == 0:
                print(f"Epoch {e + 1}/{epochs}, error={error:.6f}")

        return errors
